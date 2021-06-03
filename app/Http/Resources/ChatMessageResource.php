<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    protected $authUserID;

    public function __construct($resource, $user=0)
    {
        JsonResource::__construct($resource);
        $this->authUserID = $user;
    }

    public function toArray($request)
    {
        if(is_int($this->authUser)) {
            $this->authUserID = $request->user()->id;
        }

        $reactions = $this->reactions->groupBy('emoji_name');

        return [
            '_id' => $this->id,
            'content' => $this->message,
            'senderId' => $this->user->id,
            'username' => $this->user->username,
            'avatar' => $this->user->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $this->user->profile_picture->id]) : '/img/user.svg',
            'date' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'distributed' => 1,
            'roomId' => $this->room_id,
            'seen' => $this->seens()->where('id', '!=', $this->authUserID)->exists() ? 1 : 0,
            'replyMessage' => $this->message_id !== null ? new ChatMessageResource($this->replyMessage, $this->authUserID) : null,
            'reactions' => $reactions,
            'system' => $this->is_system ? true : false,
            'seenBy' => ChatUserResource::collection($this->seens),
        ];
    }
}
