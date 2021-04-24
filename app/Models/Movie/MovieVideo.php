<?php

namespace App\Models\Movie;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieVideo extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;

    public $table = 'movies_videos';
    public $fillable = ['movie_id', 'youtube_id', 'status', 'user_id'];

    public static $filters = ['url', 'status'];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function scopeFilter($query, $type, $filter)
    {
        switch($type) {
            case 'url':
                return $query->where('youtube_id', 'LIKE', '%'.$filter.'%');
                break;
            case 'status':
                return $query->where('status', (string) $filter);
                break;
            default:
                return $query;
                break;
        }
    }
}
