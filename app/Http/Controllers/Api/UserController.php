<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\User\UserRepositoryInterface;
use App\Http\Requests\UserAuthenticationRequest;
use Exception;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(ApiResponse $apiResponse, UserRepositoryInterface $userRepository)
    {
        $this->apiResponse = $apiResponse;
        $this->userRepository = $userRepository;
    }

    public function getUserInfo(){
        return $this->apiResponse->setSuccess(__("User_Data"))->setData(new UserResource(Auth::user()))->getJsonResponse();
    }

    public function infoUpdate(UserAuthenticationRequest $userRequest){

        try {

            DB::beginTransaction();
            $user = Auth::guard('api')->user();
            if (empty($user)) {
                return $this->apiResponse->setError(__("expired_token"))->setData()->getJsonResponse();
            }

            if ($userRequest->hasFile("avatar")) {
                $avatar = $this->userRepository->uploadAvatar($userRequest->file('avatar'));
            } else {
                $avatar = null;
            }
            $updated_user = $this->userRepository->update($user->id,array_merge($userRequest->except('avatar'),['avatar'=>$avatar]));
            DB::commit();

            return $this->apiResponse
                ->setSuccess("User_Updated_Successfully")
                ->setData(new UserResource($updated_user))
                ->getJsonResponse();

        } catch(Exception $exception){
            DB::rollback();
            return $this->apiResponse->setError($exception->getMessage())->setData()->getJsonResponse();
        }
    }
}
