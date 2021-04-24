<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieWriter extends Model
{
    use HasFactory;

    public $table = 'movies_writers';
    public $fillable = ['movie_id', 'writer_id'];
}
