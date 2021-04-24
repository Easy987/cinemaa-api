<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $roomDetails = $this->getRoomDetails();
        $lastMessage = new ChatLastMessageResource($this->lastMessage());

        return [
            "roomId" => $this->id,
            "roomName" => $roomDetails['roomName'],
            "index" => $this->lastMessage()->created_at ?? $this->created_at,
            "unreadCount" => $lastMessage && isset($lastMessage['new']) && $lastMessage['new'] ? 1 : 0,
            "avatar" => $roomDetails['avatar'],
            "lastMessage" => $lastMessage,
            "user" => new ChatUserResource($this->user),
            "users" => ChatUserResource::collection($this->users()->get())
        ];
    }

    private function getRoomDetails()
    {
        $count = $this->users()->count();
        if($count === 2){
            $otherUser = $this->users()->where('id', '!=', request()->user()->id)->first();
            if($otherUser) {
                $roomName = $otherUser->username;
                $avatar = $otherUser->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $otherUser->profile_picture->id]) : '/img/user.svg';
            } else {
                $roomName = request()->user()->username;
                $avatar = request()->user()->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => request()->user()->profile_picture->id]) : '/img/user.svg';
            }

        } elseif($count === 1) {
            $roomName = request()->user()->username;
            $avatar = request()->user()->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => request()->user()->profile_picture->id]) : '/img/user.svg';
        } else {
            $roomName = $this->name;
            $avatar = '/img/user.svg';
        }

        return ['roomName' => $roomName, 'avatar' => $avatar];
    }
}
