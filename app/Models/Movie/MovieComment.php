<?php

namespace App\Models\Movie;

use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieComment extends Model
{
    use HasFactory, SoftDeletes, UUIDTrait;

    public $table = 'movies_comments';
    public $fillable = ['movie_id', 'user_id', 'comment', 'created_at', 'updated_at', 'status'];

    public $filters = [''];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dislikes()
    {
        return $this->hasMany(MovieCommentRating::class, 'comment_id')->where('type', 0);
    }

    public function likes()
    {
        return $this->hasMany(MovieCommentRating::class, 'comment_id')->where('type', 1);
    }

    public function ratings()
    {
        return $this->hasMany(MovieCommentRating::class, 'comment_id');
    }
}
