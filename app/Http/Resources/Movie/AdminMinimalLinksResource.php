<?php

namespace App\Http\Resources\Movie;

use App\Http\Resources\AdminUserMinimalResource;
use App\Http\Resources\SiteResource;
use App\Http\Resources\UserMinimalResource;
use App\Http\Resources\UserResource;
use App\Models\Movie\LinkType;
use App\Models\Site;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMinimalLinksResource extends JsonResource
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
            'user' => new AdminUserMinimalResource($this->user),
            'status' => $this->status,
            'link' => $this->link,
            'message' => $this->message,
            'part' => $this->part,
            'season' => $this->season,
            'episode' => $this->episode,
            //'views' => views($this->resource)->count(),
            'created_at' => $this->created_at,
        ];

        if($this->movie) {
            $data['movie'] = [
                'titles' => new TitleCollection($this->movie->titles),
                'slugs' => new SlugCollection($this->movie->titles),
                'year' => $this->movie->year,
                'length' => $this->movie->length
            ];
        }

        if($this->site) {
            $data['site'] = [
                'id' => $this->site->id,
                'name' => $this->site->name,
            ];
        }

        if($this->linkType) {
            $data['linkType'] = [
                'id' => $this->linkType->id,
                'name' => $this->linkType->name,
            ];
        }

        if($this->languageType) {
            $data['languageType'] = [
                'id' => $this->languageType->id,
                'name' => $this->languageType->name,
            ];
        }

        return $data;
    }
}
