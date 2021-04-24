<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieGenre extends Model
{
    use HasFactory;

    public $table = 'movies_genres';
    public $fillable = ['movie_id', 'genre_id'];
}
