<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Http\Resources\Movie\CommentResource;
use App\Http\Resources\Movie\ItemResource;
use App\Http\Resources\Movie\MovieMinimalResource;
use App\Http\Resources\Movie\MovieResource;
use App\Models\Movie\Actor;
use App\Models\Movie\BadLink;
use App\Models\Movie\Director;
use App\Models\Movie\Genre;
use App\Models\Movie\LanguageType;
use App\Models\Movie\LinkType;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieComment;
use App\Models\Movie\MovieCommentRating;
use App\Models\Movie\MovieFavourite;
use App\Models\Movie\MovieLink;
use App\Models\Movie\MovieRating;
use App\Models\Movie\MovieToBeWatched;
use App\Models\Movie\MovieVideo;
use App\Models\Movie\MovieView;
use App\Models\Movie\MovieWatched;
use App\Models\Movie\Writer;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['only' => ['comment', 'commentLike', 'commentDelete', 'rate', 'favourites', 'watcheds', 'toBeWatcheds', 'favourite', 'watched', 'toBeWatched']]);
    }

    public function info(Request $request)
    {
        if($request->has('cinemaa-admin')) {
            return response()->json([
                'data' => [
                    'minYear' => Movie::min('year'),
                    'writers' => ItemResource::collection(Writer::orderBy('name')->get()),
                    'directors' => ItemResource::collection(Director::orderBy('name')->get()),
                    'actors' => ItemResource::collection(Actor::orderBy('name')->get()),
                    'sites' => Site::all(),
                    'languageTypes' => LanguageType::all(),
                    'linkTypes' => LinkType::all(),
                    'users' => User::select('id', 'username')->get(),
                    'genres' => ItemResource::collection(Genre::all())
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'minYear' => Movie::min('year'),
                'sites' => Site::all(),
                'languageTypes' => LanguageType::all(),
                'linkTypes' => LinkType::all(),
            ]
        ]);
    }

    public function premierMovies(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->movies()->top()->get());
    }

    public function premierSeries(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->series()->top()->get());
    }

    public function top(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->movies()->top()->get());
    }

    public function popular(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->popular()->get());
    }

    public function recommendsPremiers(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->recommendsPremiers()->get());
    }

    public function recommendsDVD(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->recommendsDVD()->get());
    }

    public function recommendsSeries(Request $request)
    {
        return MovieMinimalResource::collection(Movie::minimal()->recommendsSeries()->get());
    }

    public function all(Request $request)
    {
        $movies = Movie::minimal();

        if($request->has('type')){
            $movies = $movies->byType($request->get('type'));
        }

        $movies = $movies->active();

        if($request->hasAny(Movie::$filters)){
            $filters = $request->only(Movie::$filters);
            foreach($filters as $type => $filter) {
                $movies = $movies->filter($type, json_decode($filter, true));
            }
        }

        return MovieMinimalResource::collection($movies->paginate());
    }

    public function movies(Request $request)
    {
        $movies = Movie::minimal()->movies();

        if($request->has('type')){
            $movies = $movies->byType($request->get('type'));
        }

        $movies = $movies->active();

        if($request->hasAny(Movie::$filters)){
            $filters = $request->only(Movie::$filters);
            foreach($filters as $type => $filter) {
                $movies = $movies->filter($type, json_decode($filter, true));
            }
        }

        return MovieMinimalResource::collection($movies->paginate());
    }

    public function series(Request $request)
    {
        $series = Movie::minimal()->series();

        if($request->has('type')){
            $series = $series->byType($request->get('type'));
        }

        $movies = $series->active();

        if($request->hasAny(Movie::$filters)){
            $filters = $request->only(Movie::$filters);
            foreach($filters as $type => $filter) {
                $series = $series->filter($type, json_decode($filter, true));
            }
        }

        return MovieMinimalResource::collection($series->paginate());
    }

    public function movie(Request $request, $slug, $year, $length = null)
    {
        $movie = null;

        if ($length !== null) {
            $movie = Movie::bySlug($slug)->where('year', $year)->where('length', $length)->exists();
            if ($movie) {
                $movie = Movie::bySlug($slug)->where('year', $year)->where('length', $length)->with('titles', 'poster', 'genres', 'descriptions', 'writers', 'directors', 'actors', 'comments')->first();
            }
        }

        if (!$movie) {
            $movie = Movie::bySlug($slug)->where('year', $year)->exists();
            if ($movie) {
                $movie = Movie::bySlug($slug)->where('year', $year)->with('titles', 'poster', 'genres', 'descriptions', 'writers', 'directors', 'actors', 'comments')->first();
            }
        }

        if ($movie) {
            views($movie)->cooldown(10)->record();

            if($request->user()) {
                MovieView::firstOrCreate([
                    'user_id' => $request->user()->id,
                    'movie_id' => $movie->id,
                ], [
                    'user_id' => $request->user()->id,
                    'movie_id' => $movie->id,
                ]);
            }

            return new MovieResource($movie);
        }
    }

    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|string',
            'comment' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie_id'));

        MovieComment::create([
            'movie_id' => $movie->id,
            'user_id' => auth()->user()->id,
            'comment' => $request->get('comment'),
            'status' => (string)StatusEnum::Active,
        ]);

        return new MovieResource($movie);
    }

    public function commentLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|string',
            'type' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $commentID = $request->get('comment_id');
        $userID = auth()->user()->id;
        $type = $request->get('type');
        $comment = MovieComment::findOrFail($commentID);

        $currentRating = MovieCommentRating::where('user_id', $userID)->where('comment_id', $commentID)->first();

        if(!$currentRating) {
            MovieCommentRating::create([
                'comment_id' => $commentID,
                'user_id' => $userID,
                'type' => $type
            ]);
        } elseif($currentRating && $currentRating->type !== $type) {
            MovieCommentRating::where('user_id', $userID)->where('comment_id', $commentID)->update(['type' => $type]);
        } elseif($currentRating && $currentRating->type === $type) {
            MovieCommentRating::where('user_id', $userID)->where('comment_id', $commentID)->delete();
        }

        return CommentResource::collection(MovieComment::where('status', '1')->where('movie_id', $comment->movie_id)->get());
    }

    public function commentDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $comment = MovieComment::with('movie', 'user')->findOrFail($request->get('comment_id'));

        if($comment->user->id === $request->user()->id || $request->user()->can('comments.delete'))
        {
            $comment->delete();
        }

        return new MovieResource($comment->movie);
    }

    public function rate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|string',
            'rating' => 'required|integer|between:1,10'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie_id'));

        $userID = auth()->user()->id;
        $currentRating = MovieRating::where('user_id', $userID)->where('movie_id', $movie->id)->first();

        if($currentRating) {
            return response('', 305);
        }

        MovieRating::create([
            'movie_id' => $movie->id,
            'user_id' => $userID,
            'rating' => $request->get('rating')
        ]);

        return new MovieResource($movie);
    }

    public function favourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie_id'));

        $userID = auth()->user()->id;
        $current = MovieFavourite::where('user_id', $userID)->where('movie_id', $movie->id)->first();

        if($current) {
            MovieFavourite::where('user_id', $userID)->where('movie_id', $movie->id)->delete();
        } else {
            MovieFavourite::create([
                'movie_id' => $movie->id,
                'user_id' => $userID
            ]);
        }

        return new MovieMinimalResource($movie);
    }

    public function watched(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie_id'));

        $userID = auth()->user()->id;
        $current = MovieWatched::where('user_id', $userID)->where('movie_id', $movie->id)->first();

        if($current) {
            MovieWatched::where('user_id', $userID)->where('movie_id', $movie->id)->delete();
        } else {
            MovieWatched::create([
                'movie_id' => $movie->id,
                'user_id' => $userID
            ]);
        }

        return new MovieMinimalResource($movie);
    }

    public function toBeWatched(Request $request) {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie_id'));

        $userID = auth()->user()->id;
        $current = MovieToBeWatched::where('user_id', $userID)->where('movie_id', $movie->id)->first();

        if($current) {
            MovieToBeWatched::where('user_id', $userID)->where('movie_id', $movie->id)->delete();
        } else {
            MovieToBeWatched::create([
                'movie_id' => $movie->id,
                'user_id' => $userID
            ]);
        }

        return new MovieMinimalResource($movie);
    }

    public function favourites(Request $request)
    {
        $userID = auth()->user()->id;

        $movies = Movie::with('favourites', 'favourites.user')->whereHas('favourites.user', function(Builder $subQuery) use ($userID) {
            $subQuery->where('user_id', $userID);
        });

        if($request->has('type')){
            $movies = $movies->byType($request->get('type'));
        }

        $movies = $movies->active();

        if($request->hasAny(Movie::$filters)){
            $filters = $request->only(Movie::$filters);
            foreach($filters as $type => $filter) {
                $movies = $movies->filter($type, json_decode($filter, true));
            }
        }

        return MovieMinimalResource::collection($movies->paginate());
    }

    public function toBeWatcheds(Request $request) {
        $userID = auth()->user()->id;

        $movies = Movie::with('toBeWatched', 'toBeWatched.user')->whereHas('toBeWatched.user', function(Builder $subQuery) use ($userID) {
            $subQuery->where('user_id', $userID);
        });

        if($request->has('type')){
            $movies = $movies->byType($request->get('type'));
        }

        $movies = $movies->active();

        if($request->hasAny(Movie::$filters)){
            $filters = $request->only(Movie::$filters);
            foreach($filters as $type => $filter) {
                $movies = $movies->filter($type, json_decode($filter, true));
            }
        }

        return MovieMinimalResource::collection($movies->paginate());
    }

    public function popularAll(Request $request)
    {
        return response()->json([
              'movies' => MovieMinimalResource::collection(Movie::movies()->popular()->get()),
              'series' => MovieMinimalResource::collection(Movie::series()->popular()->get()),
        ]);
    }

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'type' => 'required|integer',
            'movie_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie_id'));
        $type = $request->get('type');
        $id = $request->get('id');

        $link = BadLink::where('user_id', $request->user()->id)->where('reportable_id', $id)->first();

        if($link) {
            return respone('', 403);
        }

        $array = [
            'reportable_type' => $type === 0 ? MovieLink::class : MovieVideo::class,
            'reportable_id' => $id,
            'type' => $type,
            'user_id' => $request->user()->id,
            'movie_id' => $movie->id,
        ];

        if($request->has('message')) {
            $array['message'] = $request->get('message');
        }

        BadLink::create($array);

        return response('', 200);
    }

    public function submitLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie' => 'required|string',
            'links' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $movie = Movie::findOrFail($request->get('movie'));
        $links = $request->get('links');

        foreach($links as $link) {
            if($link['link'] !== '') {
                $exists = MovieLink::where('link', $link['link'])->exists();

                if ($exists) {
                    return response('', 301);
                }
            }
        }

        foreach($links as $link) {
            $url = $link['link'];
            $domain = parse_url(trim($url));
            if(isset($domain['host'])) {
                $domain = Site::getDomain($domain['host'], false);

                $site = Site::where('url', $domain)->exists();

                if ($site) {
                    $linkSite = Site::where('url', $domain)->first()->id;
                } else {
                    $linkSite = null;
                }

                $linkType = LinkType::findOrFail($link['linkType']['id']);
                $languageType = LanguageType::findOrFail($link['languageType']['id']);

                if($link['link'] !== '') {
                    MovieLink::create([
                        'movie_id' => $movie->id,
                        'site_id' => $linkSite,
                        'link' => $link['link'],
                        'message' => $link['message'] ?? null,
                        'link_type_id' => $linkType->id,
                        'language_type_id' => $languageType->id,
                        'user_id' => $request->user()->id,
                        'status' => $request->user()->can('links.submit') ? '1' : '0',
                        'part' => $link['part'] ?? 0,
                        'season' => $link['season'] ?? 0,
                        'episode' => $link['episode'] ?? 0
                    ]);
                }
            }
        }
        return response('', 200);
    }
}
