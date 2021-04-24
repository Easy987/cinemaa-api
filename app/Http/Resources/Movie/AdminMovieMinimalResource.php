<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\UserResource;
use App\Models\Movie\Movie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMovieMinimalResource extends JsonResource
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
            'titles' => new TitleCollection($this->titles),
            'slugs' => new SlugCollection($this->titles),
            'imdb_rating' => $this->imdb_rating,
            'user' => new UserResource($this->user),
            'rating' => round($this->ratings->avg('rating'), 1),
            'type' => (int)$this->type,
            'year' => $this->year,
            'length' => $this->length,
            'views' => views($this->resource)->count(),
            'status' => $this->status,
            'imdb_id' => $this->imdb_id,
            'is_premier' => $this->is_premier,
            'porthu_id' => $this->porthu_id,
            'created_at' => $this->created_at,
        ];

        return $array;
    }
}
