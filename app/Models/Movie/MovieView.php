<?php

namespace App\Models\Movie;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieView extends Model
{
    use HasFactory;

    protected $table = 'movies_views';
    protected $fillable = ['movie_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
