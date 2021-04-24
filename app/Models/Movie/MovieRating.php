<?php

namespace App\Models\Movie;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieRating extends Model
{
    use HasFactory;

    public $table = 'movies_ratings';
    public $fillable = ['movie_id', 'user_id', 'rating'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
