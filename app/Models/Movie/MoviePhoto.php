<?php

namespace App\Models\Movie;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoviePhoto extends Model
{
    use HasFactory, UUIDTrait;

    public $table = 'movies_photos';
    public $fillable = ['movie_id', 'extension', 'is_poster'];
}
