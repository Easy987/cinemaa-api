<?php

namespace App\Models\Movie;

use App\Enums\StatusEnum;
use App\Models\Site;
use App\Models\User;
use App\Traits\UUIDTrait;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieLink extends Model implements Viewable
{
    use HasFactory, SoftDeletes, UUIDTrait, InteractsWithViews;

    public $table = 'movies_links';
    public $incrementing = false;
    public $fillable = ['movie_id', 'link_type_id', 'site_id', 'user_id', 'part', 'season', 'episode', 'language_type_id', 'status', 'link', 'created_at', 'updated_at'];
    public static $filters = ['url', 'status'];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function linkType()
    {
        return $this->belongsTo(LinkType::class);
    }

    public function languageType()
    {
        return $this->belongsTo(LanguageType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeFilter($query, $type, $filter)
    {
        switch($type) {
            case 'url':
                return $query->whereHas('movie.titles', function(Builder $subQuery) use ($filter) {
                $subQuery->where('lang', 'hu')->where('title', 'LIKE', '%'.$filter.'%');
                });
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
