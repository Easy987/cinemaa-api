<?php

namespace App\Models\Forum;

use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory, UUIDTrait;

    public $table = 'forum_posts';
    public $fillable = ['topic_id', 'user_id', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
