<?php

namespace App\Http\Resources\Movie;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieMinimalResource extends JsonResource
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
            'poster' => $this->poster ? route('cinema.photo', ['moviePhoto' => $this->poster->id]) : '/img/covers/cover.jpg',
            'genres' => ItemResource::collection($this->genres->take(3)),
            'imdb_rating' => $this->imdb_rating,
            'type' => (int)$this->type,
            'year' => (int)$this->year,
            'length' => (int)$this->length,
            'rating' => $this->ratings->avg('rating'),
            'highest_quality' => $this->links ? ($this->links()->first() ? $this->links()->groupBy('language_type_id')->orderBy('language_type_id', 'ASC')->join('language_types', 'language_types.id', '=', 'movies_links.language_type_id')->select('language_types.name')->get() : null ) : null,
        ];

        if($request->user()) {
            $array['rated_by_user'] = $this->ratings()->whereHas('user', function(Builder $subQuery) use ($request) {
                $subQuery->where('user_id', $request->user()->id);
            })->exists();

            $array['favourited_by_user'] = $this->favourites()->whereHas('user', function(Builder $subQuery) use ($request) {
                $subQuery->where('user_id', $request->user()->id);
            })->exists();

            $array['watched_by_user'] = $this->watched()->whereHas('user', function(Builder $subQuery) use ($request) {
                $subQuery->where('user_id', $request->user()->id);
            })->exists();

            $array['to_be_watched_by_user'] = $this->toBeWatched()->whereHas('user', function(Builder $subQuery) use ($request) {
                $subQuery->where('user_id', $request->user()->id);
            })->exists();
        }

        return $array;
    }
}
