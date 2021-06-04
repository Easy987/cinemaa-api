<?php

namespace App\Models\Movie;

use App\Enums\MovieTypeEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use App\Traits\UUIDTrait;
use CyrildeWit\EloquentViewable\Support\Period;
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Imdb\Config;
use Imdb\Exception;
use Imdb\Title;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Facades\Image;
use PHPHtmlParser\Dom;

class Movie extends Model implements Viewable
{
    use HasFactory, UUIDTrait, SoftDeletes, InteractsWithViews, EloquentJoin;

    public $perPage = 24;
    public static $filters = ['genres', 'quality', 'imdb', 'year', 'name', 'status', 'empty_links', 'premiers'];
    public $fillable = ['status', 'only_auth', 'type', 'year', 'season', 'length', 'is_premier', 'premier_date', 'imdb_id', 'imdb_rating', 'imdb_votes', 'user_id', 'porthu_id', 'created_at', 'updated_at', 'accepted_at', 'watched_at'];

    public function titles()
    {
        return $this->hasMany(MovieTitle::class);
    }

    public function getTitle()
    {
        $title = $this->titles()->where('lang', 'hu')->first();
        if($title) {
            return $title;
        }

        $title = $this->titles()->where('lang', 'en')->first();
        return $title;
    }

    public function getDescription()
    {
        $description = $this->descriptions()->where('lang', 'hu')->first();
        if($description) {
            return $description;
        }

        $description = $this->descriptions()->where('lang', 'en')->first();
        return $description;
    }

    public function descriptions()
    {
        return $this->hasMany(MovieDescription::class);
    }

    public function videos()
    {
        return $this->hasMany(MovieVideo::class);
    }

    public function ratings()
    {
        return $this->hasMany(MovieRating::class);
    }

    public function favourites()
    {
        return $this->hasMany(MovieFavourite::class);
    }

    public function watched()
    {
        return $this->hasMany(MovieWatched::class);
    }

    public function toBeWatched()
    {
        return $this->hasMany(MovieToBeWatched::class);
    }

    public function links()
    {
        return $this->hasMany(MovieLink::class)->orderBy('language_type_id');
    }

    public function poster()
    {
        return $this->hasOne(MoviePhoto::class)->where('is_poster', 1);
    }

    public function photos()
    {
        return $this->hasMany(MoviePhoto::class)->where('is_poster', 0);
    }

    public function comments()
    {
        return $this->hasMany(MovieComment::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movies_genres');
    }

    public function writers()
    {
        return $this->belongsToMany(Writer::class, 'movies_writers');
    }

    public function directors()
    {
        return $this->belongsToMany(Director::class, 'movies_directors');
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'movies_actors');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeRandom($query)
    {
        return $query->inRandomOrder();
    }

    public function scopeMinimal($query)
    {
        return $query->with('titles', 'poster', 'genres');
    }

    public function scopeActive($query)
    {
        return $query->where('movies.status', (string) StatusEnum::Active);
    }

    public function scopeTop($query)
    {
        //return $query->active()->where('type', (string) MovieTypeEnum::Movie)->orderByDesc('watched_at')->limit(10);
        return $query->active()->where('is_premier', 1)->orderBy('premier_date', 'DESC');
    }

    public function scopePopular($query)
    {
        return $query->active()->orderByViews('desc', Period::pastDays(7))->limit(18);
    }

    public function scopeMovies($query)
    {
        return $query->active()->where('type', (string) MovieTypeEnum::Movie);
    }

    public function scopeSeries($query)
    {
        return $query->active()->where('type', (string) MovieTypeEnum::Series);
    }

    public function scopeAlsoWatch($query, $movie)
    {
        return $query->active()->where('id', '!=', $movie->id)->where('type', (string)$movie->type)->whereHas('genres', function(Builder $subQuery) use ($movie) {
            $subQuery->whereIn('name', $movie->genres->pluck('name'));
        })->inRandomOrder()->limit(6);
    }

    public function scopeRecommendsPremiers($query)
    {
        return $query->active()->where('is_premier', 1)->orderBy('premier_date', 'DESC')->limit(36);
    }

    public function scopeRecommendsDVD($query)
    {
        return $query->active()->whereHas('links.linkType',  function(Builder $subQuery) {
            return $subQuery->where('status', (string) StatusEnum::Active)->where('name', '=', 'dvd');
        })->orderBy('created_at', 'DESC')->limit(36);
    }

    public function scopeRecommendsSeries($query)
    {
        return $query->active()->series()->orderByViews('desc', Period::pastDays(7))->limit(36);
    }

    public function scopeFilter($query, $type, $filter)
    {
        switch($type) {
            case 'genres':
                return $query->whereHas('genres', function (Builder $query) use ($filter) {
                    $query->where(function($query) use ($filter){
                        foreach($filter as $genreFilter) {
                            $query->orWhere('name', $genreFilter['key']);
                        }
                    });
                });
                break;
            case 'imdb':
                return $query->whereBetween('imdb_rating', $filter);
                break;
            case 'year':
                if($filter[0] === "" && $filter[1] === "") {
                    return $query;
                }

                if($filter[0] === "" && $filter[1] !== "") {
                    return $query->where('year', '<', $filter[1]);
                } else if($filter[1] === "" && $filter[0] !== "") {
                    return $query->where('year', '>', $filter[0]);
                } else {
                    return $query->whereBetween('year', $filter);
                }

                break;
            case 'quality':
                return $query->whereHas('links', function(Builder $subQuery) {
                    $subQuery->where('status', (string) StatusEnum::Active);
                })->whereHas('links.linkType', function (Builder $subQuery) use ($filter) {
                    $subQuery->where(function($query) use ($filter){
                        foreach($filter as $genreFilter) {
                            $query->orWhere('name', $genreFilter['key']);
                        }
                    });
                });
                break;
            case 'name':
                return $query->whereHas('titles', function(Builder $subQuery) use ($filter) {
                    $subQuery->where('title', 'LIKE', '%'.$filter.'%');
                });
                break;
            case 'status':
                return $query->where('status', (string) $filter);
                break;
            case 'empty_links':
                return $query->where(function($q) {
                    $q->doesntHave('links');
                })->orWhere(function($q){
                    $q->whereHas('links', function($q){
                        $q->where('status', '=', '1')->havingRaw('COUNT(*) = 0');
                    });
                })->orderByJoin('links.updated_at', 'DESC')->orderByJoin('links.created_at', 'DESC');
                break;
            case 'premiers':
                return $query->where('is_premier', 1)->orderBy('premier_date', 'DESC');
                break;
            default:
                return $query;
                break;
        }
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->whereHas('titles', function (Builder $query) use ($slug) {
            $query->where('slug', $slug);
        });
    }

    public function scopeByType($query, $type)
    {
        switch($type) {
            case 'new-links':
                return $query->orderByJoin('links.created_at', 'DESC');
                break;
            case 'newest':
                return $query->orderByDesc('created_at');
                break;
            case 'most-watched':
                return $query->orderByViews('desc', Period::pastDays(7));
                break;
            case 'best':
                return $query->orderByDesc('imdb_rating');
                break;
        }
    }

    public static function download($imdbID, $porthuID, $userID) {
        /* Initalizing IMDB */
        try {
            $config = new Config();
            $config->language = 'hu-HU,hu,en';
            $imdb = new Title($imdbID, $config);

            $realID = $imdb->real_id();

            $httpClient = new Client();
        } catch (Exception $e) {
            throw new Exception('IMDB ID not found', 404);
        }

        /* Saving model */
        $movie = self::updateOrCreate(['imdb_id' => $imdbID],
			[
            'type' => $imdb->is_serial() ? (string)MovieTypeEnum::Series : (string)MovieTypeEnum::Movie,
            'year' => $imdb->year() ?? 0,
            'season' => $imdb->seasons() ?? 0,
            'length' => $imdb->runtime() ?? 0,
            'imdb_id' => $imdbID,
            'imdb_rating' => $imdb->rating() ?? 0,
            'imdb_votes' => $imdb->votes() ?? 0,
            'user_id' => $userID,
            'porthu_id' => $porthuID,
        ]);

        /* Saving titles */
        \App\Models\Movie\MovieTitle::updateOrCreate(['movie_id' => $movie->id, 'lang' => 'hu'],
			[
            'movie_id' => $movie->id,
            'lang' => 'hu',
            'title' => $imdb->title()
        ]);

        \App\Models\Movie\MovieTitle::updateOrCreate(['movie_id' => $movie->id, 'lang' => 'en'],
			[
            'movie_id' => $movie->id,
            'lang' => 'en',
            'title' => Str::length($imdb->orig_title()) === 0 ? $imdb->title() : $imdb->orig_title()
        ]);

        /* Saving plots */
        $plots = collect($imdb->plot_split())->filter(static function($plot) {
            return Str::length($plot['plot']) >= config('cinema.plot_minimum_length');
        })->take(1)->map(static function($plot) {
            return ['lang' => 'en', 'description' => $plot['plot']];
        });

        $needsMafab = false;

        if($porthuID !== null && $porthuID != '') {
            try {
                $res = $httpClient->get('https://port.hu/adatlap/film/tv/movie/movie-' . $porthuID);
            } catch (ClientException $exception) {
                $needsMafab = true;
            }

            if(!$needsMafab) {
                $dom = new Dom();
                $dom->loadStr($res->getBody());
                $descriptions = $dom->find('.description article');

                if($descriptions->count() > 0) {
                    $plots->add(['lang' => 'hu', 'description' => trim($descriptions[0]->firstChild()->text)]);
                }
            }
        }

        if(( $porthuID === null || strlen($porthuID) === 0 ) || $needsMafab){
            $movieTitle = $imdb->orig_title() !== '' ? $imdb->orig_title() : $imdb->title();

            $origTitle = str_replace('/', ' ', $movieTitle);

            $usableURL = ('https://www.mafab.hu/search/&search=' . urlencode($origTitle));



            $dom = new Dom();
            $dom->loadStr(file_get_contents($usableURL));
            $foundMovies = $dom->find('.col-xs-13 .movie_title_link');

            foreach($foundMovies as $index => $mafabMovie) {
                $href = $dom->find('.col-xs-13 .movie_title_link')[$index]->getAttribute('href');
                $title = $dom->find('.col-xs-13 small[style="font-size:11px;"]')[$index]->firstChild()->text;
                $year = $dom->find('.col-xs-13 .pull-left')[$index*2]->firstChild()->text;

                $movieLink = 'https://www.mafab.hu' . $href;

                if(Str::contains($movieLink, Str::slug($movieTitle)) || (Str::contains($title, $movieTitle) && Str::contains($year, $imdb->year()))) {
                    $dom = new Dom();
                    $dom->loadStr(file_get_contents($movieLink));

                    $content = $dom->find('.bio-content p');
                    if(isset($content[0])) {
                        $hungarianPlot = html_entity_decode($content[0]->firstChild()->text);

                        $plots->add(['lang' => 'hu', 'description' => trim($hungarianPlot)]);
                    } else {
                        preg_match('/(\d+).html/', $href, $matches);

                        $mafabMovieID = $matches[1];

                        $plotContent = $httpClient->request('POST', 'https://www.mafab.hu/includes/jquery/movies_ajax.php',
                            [
                                'form_params' => [
                                    'request' => 'official_bio',
                                    'movie_id' => $mafabMovieID,
                                ]
                            ]);

                        $plotContent = $plotContent->getBody()->getContents();

                        $dom = new Dom();
                        $dom->loadStr($plotContent);
                        $plot = $dom->find('.biobox_full_official p');

                        if(isset($plot[0])) {
                            $hungarianPlot = html_entity_decode($plot[0]->firstChild()->text);

                            $plots->add(['lang' => 'hu', 'description' => trim($hungarianPlot)]);
                        }
                    }

                    break;
                }
            }

        }

        foreach($plots as $plot) {
            MovieDescription::updateOrCreate(['movie_id' => $movie->id, 'lang' => $plot['lang']],
				[
                'movie_id' => $movie->id,
                'lang' => $plot['lang'],
                'description' => $plot['description']
            ]);
        }

        /* Creating directories */
        $basePath = storage_path() . '/images/movies/' . $movie->id;

        File::makeDirectory($basePath, 0777, true, true);
        File::makeDirectory($basePath . '/photos', 0777, true, true);

        /* Saving pictures PORT */

        $needsPictures = false;

        if($porthuID !== null && strlen($porthuID) > 0) {
            try {
                $res = $httpClient->get('https://port.hu/adatlap/film/tv/movie/movie-' . $porthuID);
            } catch (ClientException $exception) {
                $needsPictures = true;
            }

            if(!$needsPictures) {
                $dom = new Dom();
                $dom->loadStr($res->getBody());

                $pictures = collect($dom->find('meta[property="og:image"]'))->map(function($image) {
                    return ['url' => $image->tag->getAttribute('content')->getValue(), 'is_poster' => 0];
                });

                $pictures = $pictures->map(static function($image) {
                    $extension = pathinfo($image['url'], PATHINFO_EXTENSION);

                    if(!isset($image['is_poster'])) {
                        $extension = 'jpg';
                    }
                    return ['src' => $image['url'], 'extension' => $extension, 'is_poster' => $image['is_poster'] ?? 0];
                });
            }
        }

        if(( $porthuID === null || strlen($porthuID) === 0 ) || $needsPictures){
            $pictures = collect($imdb->mainPictures())
                ->map(static function($image) {
                    $extension = pathinfo($image['bigsrc'], PATHINFO_EXTENSION);

                    if(!isset($image['is_poster'])) {
                        $extension = 'jpg';
                    }
                    return ['src' => $image['bigsrc'], 'extension' => $extension, 'is_poster' => $image['is_poster'] ?? 0];
                });
        }

        $pictures = $pictures->take(3);
        $posterPicture = $imdb->photo(false);
        $posterPictureExtension = pathinfo($posterPicture, PATHINFO_EXTENSION);
        $posterPictureModel = ['src' => $posterPicture, 'extension' => $posterPictureExtension, 'is_poster' => 1];

        $pictures->push($posterPictureModel);

        $existingPhotos = MoviePhoto::where('movie_id', $movie->id)->get();
        foreach($existingPhotos as $existingPhoto) {
            if($existingPhoto->is_poster) {
                File::delete($basePath . '/poster.' . $existingPhoto['extension']);
            } else {
                File::delete($basePath . '/photos/' . $existingPhoto->id . '.' . $existingPhoto['extension']);
            }

            $existingPhoto->delete();
        }

        foreach($pictures as $picture) {
            $moviePhoto = MoviePhoto::create([
                'movie_id' => $movie->id,
                'extension' => $picture['extension'],
                'is_poster' => $picture['is_poster'],
            ]);

            try {
                $imagePath = $basePath . '/photos/' . $moviePhoto->id . '.' . $picture['extension'];

                $image = Image::make($picture['src']);
                if($moviePhoto->is_poster) {
                    $imagePath = $basePath . '/poster.' . $picture['extension'];
                    $image = $image->resize(270, 400);
                } else {
                    $image = $image->encode('jpg', 25);
                }

                $image->save($imagePath);
            } catch (ImageException $exception) {
                $moviePhoto->delete();
            }
        }

        /* Saving genres */
        $genres = collect($imdb->genres())->map(static function($genre) {
            return mb_strtolower($genre);
        })->take(3);

        $movieGenres = Genre::whereIn('name', $genres)->get();

		MovieGenre::where('movie_id', $movie->id)->delete();

        foreach($movieGenres as $genre) {
            MovieGenre::create([
                'movie_id' => $movie->id,
                'genre_id' => $genre->id,
            ]);
        }

        /* Saving writers */
		MovieWriter::where('movie_id', $movie->id)->delete();

        foreach($imdb->writing() as $writer) {
            $writer = Writer::firstOrCreate(['name' => $writer['name']]);

            MovieWriter::create([
                'movie_id' => $movie->id,
                'writer_id' => $writer->id,
            ]);
        }
        /* Saving directors */

		MovieDirector::where('movie_id', $movie->id)->delete();
        foreach($imdb->director() as $director) {
            $director = Director::firstOrCreate(['name' => $director['name']]);

            MovieDirector::create([
                'movie_id' => $movie->id,
                'director_id' => $director->id,
            ]);
        }

        /* Saving actors */
        $actors = collect($imdb->cast(true));

		MovieActor::where('movie_id', $movie->id)->delete();
        foreach($actors as $actor) {
            $actor = Actor::firstOrCreate(['name' => $actor['name']]);

            MovieActor::create([
                'movie_id' => $movie->id,
                'actor_id' => $actor->id,
            ]);
        }
    }
}
