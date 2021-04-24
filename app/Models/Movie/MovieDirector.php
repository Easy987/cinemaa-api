<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieDirector extends Model
{
    use HasFactory;

    public $table = 'movies_directors';
    public $fillable = ['movie_id', 'director_id'];
}
