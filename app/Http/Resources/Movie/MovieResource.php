<?php

namespace App\Http\Resources\Movie;

use App\Models\Movie\Movie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'rating' => round($this->ratings->avg('rating'), 1),
            'type' => (int)$this->type,
            'descriptions' => new DescriptionCollection($this->descriptions),
            'writers' => ItemResource::collection($this->writers->take(3)),
            'directors' => ItemResource::collection($this->directors->take(3)),
            'videos' => $this->videos,
            'photos' => $this->photos->map(static function($photo) {
                return route('cinema.photo', ['moviePhoto' => $photo->id]);
            }),
            'actors' => ItemResource::collection($this->actors->take(10)),
            'comments' => CommentResource::collection($this->comments->where('status', 1)),
            'year' => $this->year,
            'length' => $this->length,
            'also_watch' => MovieMinimalResource::collection(Movie::alsoWatch($this)->get()),
            'views' => views($this->resource)->count(),
            'porthu_id' => $this->porthu_id ?? '',
            'imdb_id' => $this->imdb_id ?? ''
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
