<?php

namespace App\Http\Resources\Movie;

use App\Models\Movie\Movie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMovieResource extends JsonResource
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
            'genres' => ItemResource::collection($this->genres),
            'imdb_rating' => $this->imdb_rating,
            'rating' => $this->ratings->avg('rating'),
            'type' => (int)$this->type,
            'descriptions' => new DescriptionCollection($this->descriptions),
            'writers' => ItemResource::collection($this->writers),
            'directors' => ItemResource::collection($this->directors),
            'videos' => $this->videos,
            /*'photos' => $this->photos->map(static function($photo) {
                return route('cinema.photo', ['moviePhoto' => $photo->id]);
            }),*/
            'actors' => ItemResource::collection($this->actors->take(10)),
            //'comments' => CommentResource::collection($this->comments->where('status', 1)),
            'year' => $this->year,
            'length' => $this->length,
            //'also_watch' => MovieMinimalResource::collection(Movie::alsoWatch($this)->get()),
            //'views' => views($this->resource)->count(),
            'status' => $this->status,
            'imdb_id' => $this->imdb_id,
            'is_premier' => $this->is_premier,
            'porthu_id' => $this->porthu_id,
            'created_at' => $this->created_at,
        ];

        if($request->user()->hasRole('uploader')) {
            $links = $this->links->where('user_id', $request->user()->id)->get();
        } else {
            $links = $this->links;
        }
        $array['links'] = AdminMinimalLinkResource::collection($links);

        return $array;
    }
}
