<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\SiteResource;
use App\Http\Resources\UserResource;
use App\Models\Movie\LinkType;
use App\Models\Site;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
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
            'site' => new SiteResource($this->site),
            'linkType' => $this->linkType,
            'languageType' => $this->languageType,
            'user' => new UserResource($this->user),
            'movie' => new MovieResource($this->movie),
            'status' => $this->status,
            'link' => $this->link,
            'part' => $this->part,
            'season' => $this->season,
            'episode' => $this->episode,
            'created_at' => $this->created_at,
        ];
    }
}
