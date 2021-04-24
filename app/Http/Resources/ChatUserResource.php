<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "_id" => $this->id,
            "username" => $this->username,
            "avatar" => $this->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $this->profile_picture->id]) : '/img/user.svg',
            "status" => [
                "state" => "offline",
            ]
        ];
    }
}
