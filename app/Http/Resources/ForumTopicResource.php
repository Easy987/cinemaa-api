<?php

namespace App\Http\Resources;

use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumPostSeen;
use App\Models\Forum\ForumTopic;
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
            'posts_last_page' => $this->posts()->paginate()->lastPage(),
            'seen' => $this->posts->count() > 0 ? ForumPostSeen::where('user_id', $request->user()->id)->where('post_id', $this->posts()->latest()->first()->id)->exists() : true
        ];
    }
}
