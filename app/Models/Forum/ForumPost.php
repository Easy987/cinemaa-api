<?php

namespace App\Models\Forum;

use App\Http\Resources\ForumPostResource;
use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory, UUIDTrait;

    public $table = 'forum_posts';
    public $fillable = ['topic_id', 'user_id', 'message'];

    public $perPage = 30;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class);
    }

    public function dislikes()
    {
        return $this->hasMany(ForumPostReaction::class, 'post_id')->where('type', 0);
    }

    public function likes()
    {
        return $this->hasMany(ForumPostReaction::class, 'post_id')->where('type', 1);
    }

    public function ratings()
    {
        return $this->hasMany(ForumPostReaction::class, 'post_id');
    }
}
