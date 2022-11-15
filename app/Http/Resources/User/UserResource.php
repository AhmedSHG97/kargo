<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $avatar = null;
        if ( $this->avatar) {

            if (File::exists( public_path( $this->avatar ) ))
                $avatar = url($this->avatar);
            else
                $avatar = url('uploads/user/'.$this->avatar);
        }

        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "gender" => $this->gender? $this->gender : null,
            "avatar" => $avatar,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
