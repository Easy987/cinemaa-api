<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
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
            'secret_uuid' => $this->secret_uuid,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->roles->count() > 0 ? $this->roles->first()->name : 'user',
            'permissions' => PermissionResource::collection($this->getAllPermissions()),
            'picture' => $this->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $this->profile_picture->id]) : '/img/user.svg',
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'about' => $this->about,
            'public_name' => $this->public_name === 1,
            'created_at' => $this->created_at,
            'last_activity_at' => $this->last_activity_at ?? $this->created_at,
            'status' => $this->status,
            'comments_count' => $this->comments->count()
        ];
    }
}
