<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieActor extends Model
{
    use HasFactory;

    public $table = 'movies_actors';
    public $fillable = ['movie_id', 'actor_id'];
}
