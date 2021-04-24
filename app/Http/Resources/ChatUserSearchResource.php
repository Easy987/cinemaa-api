<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ChatUserSearchResource extends JsonResource
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
            "roomId" => Str::uuid(),
            "roomName" => $this->username,
            "index" => 0,
            "unreadCount" => 0,
            "avatar" => $this->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $this->profile_picture->id]) : '/img/user.svg',
            "lastMessage" => '',
            "users" => ChatUserResource::collection([$this])
        ];
    }
}
