<?php

namespace App\Http\Resources\Movie;

use Illuminate\Http\Resources\Json\JsonResource;

class MovieVideoResource extends JsonResource
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
            'movie' => new AdminMovieResource($this->movie),
            'youtube_id' => $this->youtube_id,
            'created_at' => $this->created_at
        ];
    }
}
