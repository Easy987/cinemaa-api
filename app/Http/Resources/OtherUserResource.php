<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OtherUserResource extends JsonResource
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
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->roles->first() ? $this->roles->first()->name : 'user',
            'picture' => $this->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $this->profile_picture->id]) : '/img/user.svg',
            'created_at' => $this->created_at,
            'last_login_at' => $this->last_login_at,
            'last_activity_at' => $this->last_activity_at ? Carbon::parse($this->last_activity_at)->diffForHumans() : Carbon::parse($this->created_at)->diffForHumans(),
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
        ];
    }
}
