<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\UserMinimalResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'like' => $this->likes->count(),
            'dislike' => $this->dislikes->count(),
            'comment' => $this->comment,
            'user' => new UserMinimalResource($this->user),
            'movie' => new MovieMinimalResource($this->movie),
            'created_at' => $this->created_at
        ];

        if($request->user()) {
            $array['rated_by_user'] = $this->ratings->where('user_id', $request->user()->id)->first() ? $this->ratings->where('user_id', $request->user()->id)->first()->type : -1;
        }

        return $array;
    }
}
