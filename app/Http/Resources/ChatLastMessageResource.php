<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class ChatLastMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected $authUser;

    public function __construct($resource, $user=0)
    {
        JsonResource::__construct($resource);
        $this->authUser = $user;
    }

    public function toArray($request)
    {
        if(is_int($this->authUser)) {
            $this->authUser = $request->user();
        }

        return [
            "content" => $this->message,
            "senderId" => $this->user->id,
            "username" => $this->user->username,
            "timestamp" => Carbon::parse($this->created_at)->diffForHumans(),
            "datetime" => $this->created_at,
            "distributed" => 1,
            'seen' => $this->seens()->where('id', '!=', $this->authUser->id)->exists() ? 1 : 0,
            'new' => $this->user_id === $this->authUser->id ? false : ($this->seens()->where('id', '=', $this->authUser->id)->exists() ? 0 : 1),
        ];
    }
}
