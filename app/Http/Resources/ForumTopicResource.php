<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumTopicResource extends JsonResource
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
            'discussion_id' => $this->discussion_id,
            'name' => $this->name,
            'description' => $this->description,
            'views' => views($this->resource)->count(),
            'posts' => $this->posts_count,
        ];
    }
}
