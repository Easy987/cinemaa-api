<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\UserResource;
use App\Models\Movie\MovieLink;
use Illuminate\Http\Resources\Json\JsonResource;

class BadLinkResource extends JsonResource
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
            'user' => new UserResource($this->user),
            'type' => $this->type,
            'created_at' => $this->created_at
        ];

        if($this->reportable_type === MovieLink::class) {
            if(isset($this->reportable->link)) {
                $array['reportable'] = [
                    'link' => $this->reportable->link
                ];
            }

        } else {
            if(isset($this->reportable->youtube_id)) {
                $array['reportable'] = [
                    'youtube_id' => $this->reportable->youtube_id
                ];
            }
        }

        if(isset($this->movie->type)) {
            $array['movie'] = [
                'type' => $this->movie->type,
                'titles' => new TitleCollection($this->movie->titles),
                'slugs' => new SlugCollection($this->movie->titles),
                'year' => $this->movie->year,
                'length' => $this->movie->length
            ];
        }

        return $array;
    }
}
