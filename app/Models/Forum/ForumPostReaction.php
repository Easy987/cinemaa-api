<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPostReaction extends Model
{
    use HasFactory;

    public $table = 'forum_posts_reactions';
}
