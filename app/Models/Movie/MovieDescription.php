<?php

namespace App\Models\Movie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieDescription extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'movies_descriptions';
    public $fillable = ['movie_id', 'lang', 'description'];
    public $incrementing = false;
}
