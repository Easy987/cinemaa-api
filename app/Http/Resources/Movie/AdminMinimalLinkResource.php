<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\SiteResource;
use App\Http\Resources\UserResource;
use App\Models\Movie\LinkType;
use App\Models\Site;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMinimalLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'site' => new SiteResource($this->site),
            'linkType' => $this->linkType,
            'languageType' => $this->languageType,
            'user' => new UserResource($this->user),
            'status' => $this->status,
            'link' => $this->link,
            'part' => $this->part,
            'season' => $this->season,
            'episode' => $this->episode,
            'views' => views($this->resource)->count(),
            'created_at' => $this->created_at,
        ];

        if($this->movie) {
            $data['movie'] = [
                'titles' => new TitleCollection($this->movie->titles),
                'slugs' => new SlugCollection($this->movie->titles),
                'year' => $this->movie->year
            ];
        }

        return $data;
    }
}
