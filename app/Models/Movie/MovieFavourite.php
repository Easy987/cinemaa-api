<?php

namespace App\Models\Movie;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieFavourite extends Model
{
    use HasFactory;

    public $table = 'movies_favourites';
    public $fillable = ['movie_id', 'user_id', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
