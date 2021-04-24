<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieCommentRating extends Model
{
    use HasFactory;

    public $table = 'movies_comments_ratings';
    public $fillable = ['comment_id', 'user_id', 'type'];
}
