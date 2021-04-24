<?php

namespace App\Models\Forum;

use App\Traits\UUIDTrait;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumDiscussion extends Model implements Viewable
{
    use HasFactory, UUIDTrait, InteractsWithViews;

    public $table = 'forum_discussions';

    public $fillable = ['name', 'icon', 'description'];

    public function topics()
    {
        return $this->hasMany(ForumTopic::class, 'discussion_id');
    }

    public function posts()
    {
        return $this->hasManyThrough(ForumPost::class, ForumTopic::class, 'discussion_id', 'topic_id', 'id', 'id');
    }
}
