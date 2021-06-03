<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'user' => new OtherUserResource($this->user),
            'message' => $this->message,
            'like' => $this->likes->count(),
            'dislike' => $this->dislikes->count(),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
        ];

        if($request->user()) {
            $array['rated_by_user'] = $this->ratings->where('user_id', $request->user()->id)->first() ? $this->ratings->where('user_id', $request->user()->id)->first()->type : -1;
        }

        return $array;
    }
}
