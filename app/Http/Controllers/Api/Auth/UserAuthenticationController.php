<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use GuzzleHttp\Client;
use App\Repositories\User\UserRepositoryInterface;
use App\Events\SignUpVerificationMailEvent;
use App\Events\UserResetPasswordEvent;
use App\Http\Requests\UserAuthenticationRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Wallet;
use App\Services\SocialUserResolver;
use Exception;
use Laravel\Passport\Client as PassportClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAuthenticationController extends Controller
{
    public function __construct(ApiResponse $apiResponse, UserRepositoryInterface $userRepository, Client $client)
    {
        $this->apiResponse = $apiResponse;
        $this->userRepository = $userRepository;
        $this->client = $client;
    }

    public function register(UserAuthenticationRequest $userAuthenticationRequest)
    {
        DB::beginTransaction();
        try {
            if($userAuthenticationRequest->hasFile("avatar")){
                $avatar = $this->userRepository->uploadAvatar($userAuthenticationRequest->file('avatar'));
            }else{
                $avatar = null;
            }
            $user = $this->userRepository->create(array_merge($userAuthenticationRequest->except('avatar'), ['avatar' => $avatar, "password" => $userAuthenticationRequest->password]));
            event(new SignUpVerificationMailEvent($userAuthenticationRequest->validated()));
            DB::commit();
            return $this->apiResponse->setSuccess(__("check_your_email_to_complete_registeration"))->setData(new UserResource($user))->getJsonResponse();
        } catch (Exception $exception) {
            DB::rollback();
            return $this->apiResponse->setError(__($exception->getMessage()))->setData()->getJsonResponse();
        }
    }

    public function verifyUser(UserAuthenticationRequest $request)
    {
        $user =  $this->userRepository->getUserByToken($request->verification_code);
        if ($user == null) {
            return $this->apiResponse->setError(__("token_expired"))->setData()->getJsonResponse();
        }
        if ($user->hasVerifiedEmail()) {
            return $this->apiResponse->setError(__("Account already verified"))->setData()->getJsonResponse();
        }

        $user->markEmailAsVerified();

        return $this->apiResponse
            ->setSuccess(__("email has been verified successfully"))
            ->setData(new UserResource($user))
            ->getJsonResponse();
    }

    public function resendVerification(UserAuthenticationRequest $request){
        $user = $this->userRepository->model->where('email',$request->email)->first();
        if ($user->hasVerifiedEmail()) {
            return $this->apiResponse->setError(__("Account already verified"))->setData()->getJsonResponse();
        }
        event(new SignUpVerificationMailEvent($user->toArray()));
        return $this->apiResponse->setSuccess(__("Verification code has been sent to your email"))->setData($user)->getJsonResponse();
    }

    public function login(UserAuthenticationRequest $userAuthenticationRequest)
    {
        $user = $this->userRepository->getUserByEmail($userAuthenticationRequest->email);

        if (is_null($user)) {
            return $this->apiResponse->setError(__("user_not_exists"))->setData()->getJsonResponse();
        }

        if (!$user->hasVerifiedEmail()) {
            return $this->apiResponse
                ->setError(__("not_verified"))->setData(new UserResource($user))->getJsonResponse();
        }

        if (!Auth::attempt($userAuthenticationRequest->only("email", "password"))) {
            return $this->apiResponse
                ->setError(__("not_authorized"))->setData()->getJsonResponse();
        }

        $passportClient = getPassportClient();
        $formParams = [
            'grant_type' => 'password',
            'client_id' => $passportClient ? $passportClient->id : '',
            'client_secret' => $passportClient? $passportClient->secret: '',
            'username' => $userAuthenticationRequest->email,
            'password' => $userAuthenticationRequest->password,
            'scope' => '*'
        ];

        try {

            $passportResponse = $this->client->request('POST', url("/oauth/token"), ['form_params' => $formParams]);
            $passportResponse = json_decode((string) $passportResponse->getBody(), true);
            $finalResponse = array_merge($passportResponse, ["user" => new UserResource($user) ]);

            return $this->apiResponse
                ->setSuccess(__("success_login"))
                ->setData($finalResponse)
                ->getJsonResponse();

        } catch (Exception $exception) {
            return $this->apiResponse
                ->setError(
                    $exception->getMessage()
                    . ' at line '
                    . $exception->getLine()
                    . ' at file '
                    . $exception->getFile()
                )
                ->setData()
                ->getJsonResponse();
        }
    }

    public function resetPassword(UserAuthenticationRequest $userAuthenticationRequest)
    {
        $user = $this->userRepository->getUserByEmail($userAuthenticationRequest->email);
        if (!$user->hasVerifiedEmail()) {
            return $this->apiResponse->setError(__("not_verified"))->setData()->getJsonResponse();
        }

        event(new UserResetPasswordEvent($user));
        unset($user->token);
        return $this->apiResponse->setSuccess(__("email_sent_successfully"))->setData(new UserResource($user))->getJsonResponse();
    }

    public function changePassword(UserAuthenticationRequest $userAuthenticationRequest)
    {
        $user =  $this->userRepository->getUserByToken($userAuthenticationRequest->otp);
        if ($user == null) {
            return $this->apiResponse->setError(__("expired_token"))->setData()->getJsonResponse();
        }
        $user->update([
            "password" => $userAuthenticationRequest->password,
        ]);
        DB::table('password_resets')->where('token', $user->email)->delete();
        return $this->apiResponse->setSuccess(__("password_changed_successfully"))->setData(new UserResource($user))->getJsonResponse();
    }

    public function updatePassword(UserAuthenticationRequest $userAuthenticationRequest)
    {
        $user =  Auth::user();
        if (is_null($user)) {
            return $this->apiResponse->setError(__("expired_token"))->setData()->getJsonResponse();
        }

        $user->update([
            "password" => $userAuthenticationRequest->password,
        ]);

        return $this->apiResponse->setSuccess(__("password_changed_successfully"))
            ->setData(new UserResource($user))
            ->getJsonResponse();
    }

    public function refreshToken(Request $request)
    {
        if (is_null($request->header('refresh-token'))) {
            return $this->apiResponse->setError(__("not_authorized"))->setData()->getJsonResponse();
        }
        $refresh_token = $request->header('refresh-token');
        $passportClient = getPassportClient();

        $formParams = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $passportClient->id??'',
            'client_secret' => $passportClient->secret??'',
            'scope' => ''
        ];

        try {
            $passportResponse = $this->client->request('POST', url("/oauth/token"), ['form_params' => $formParams]);
            $passportResponse = json_decode((string) $passportResponse->getBody(), true);
            return $this->apiResponse->setSuccess(__("token_refreshed"))->setData($passportResponse)->getJsonResponse();
        } catch (Exception $exception) {
            return $this->apiResponse->setError(__($exception->getMessage()))->setData()->getJsonResponse();
        }
    }
    public function socialLogin(UserAuthenticationRequest $userRequest, SocialUserResolver $socialUserResolver)
    {
        try {
            $user = $socialUserResolver->resolveUserByProviderCredentials($userRequest->provider, $userRequest->access_token);
            $passportClient = getPassportClient();
            $formParams = [
                'grant_type' => 'social',
                'client_id' => $passportClient->id??'',
                'client_secret' => $passportClient->secret??'',
                'provider' => $userRequest->provider,
                'access_token' => $userRequest->access_token,
            ];
            $response = $this->client->post(url('oauth/token'), ['form_params' => $formParams]);
            $token_data = json_decode($response->getBody()->getContents(), true);
            $response =  array_merge($token_data,['user' => $user]);
            return $this->apiResponse
                ->setSuccess(__("api.logged_in_successfully"))
                ->setData($response)
                ->getJsonResponse();
        } catch (Exception $exception) {
            return $this->apiResponse->setError(__($exception->getMessage()))->setData()->getJsonResponse();
        }
    }
}
