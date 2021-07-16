<?php

namespace App\Models\Forum;

use App\Traits\UUIDTrait;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model implements Viewable
{
    use HasFactory, UUIDTrait, InteractsWithViews;

    public $table = 'forum_topics';
    public $fillable = ['discussion_id', 'name', 'description'];

    public function discussion()
    {
        return $this->belongsTo(ForumDiscussion::class, 'discussion_id');
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class, 'topic_id');
    }
}
