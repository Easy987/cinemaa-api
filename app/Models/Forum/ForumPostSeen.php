<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPostSeen extends Model
{
    use HasFactory;

    protected $table = 'forum_posts_seen';
    protected $fillable = ['post_id', 'user_id'];
}
