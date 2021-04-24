<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieTitle extends Model
{
    use HasFactory;

    public $table = 'movies_titles';
    public $fillable = ['movie_id', 'lang', 'title', 'slug'];
}
