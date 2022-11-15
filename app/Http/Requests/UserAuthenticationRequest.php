<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;


class UserAuthenticationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->path() == "api/login") {
            return [
                "email" => "required|email|min:8|max:191|exists:users,email",
                "password" => [
                    'required',
                    'min:6',
                    "max:191",
                ],
            ];
        }
        if ($this->path() == "api/register") {
            return [
                "email" => "required|email|unique:users,email|min:8|max:191",
                "password" => [
                    'required',
                    'min:6',
                    "max:191",
                    'confirmed'
                ],
                "name" => "required|min:4|max:191",
                "avatar" => "sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
            ];
        }
        if ($this->path() == "api/verify") {
            return [
                "verification_code" => "required|string|min:4|max:4",
                "user_id" => "required|integer|min:1|exists:users,id",
            ];
        }
        if ($this->path() == "api/resendVerification") {
            return [
                "email" => "required|email|exists:users,email",
            ];
        }

        if ($this->path() == "api/reset/password") {
            return [
                "email" => "required|email|min:8|max:191|exists:users,email",
            ];
        }
        if ($this->path() == "api/change/password") {
            return [
                "otp" => "required|min:3|max:4|exists:password_resets,token",
                "password" => [
                    'required',
                    'min:6',
                    "max:191",
                    'confirmed'
                ],
            ];
        }

        if ($this->path() == "api/password/update") {
            return [
                "password" => [
                    'required',
                    'min:6',
                    "max:191",
                    'confirmed'
                ],
            ];
        }

        if ($this->path() == "api/me/info/update") {
            return [
                "name" => "required|string|min:3",
                "gender" => "sometimes|nullable|string|in:male,female",
                "avatar" => "sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            ];
        }
        if ($this->path() == "api/social/login") {
            return [
                "access_token" => "required|string",
                "provider" => "required|string",
            ];
        }



    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ],
                400
            )
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'status' => false,
                    'message' => "Error: you are not authorized or do not have the permission",
                    'data' => null
                ],
                401
            )
        );
    }


    public function messages()
    {
        return [
            "email.required" => __('api.email_required'),
            "email.unique" =>  __("api.user_already_exists"),
            "email.email" => __("api.not_valid_email"),
            "email.min" => __("api.email_min"),
            "email.max" => __("api.email_max"),
            "email.exists" => __("api.user_not_exists"),
            "id.exists" =>  __("api.user_not_exists"),
            "password.required" => __("api.password_required"),
            "password.confirmed" => __("api.password_confirmed"),
            "password.min" => __("api.password_min"),
            "password.max" => __("api.password_max"),
            "password.regex" => __("api.password_regex"),
            "name.required" => __("api.name_required"),
            "name.min" =>  __("api.name_min"),
            "name.max" =>  __("api.name_max"),
            "token.required" => __("api.token_required"),
            "token.exists" => __("api.token_exists"),
        ];
    }
}
