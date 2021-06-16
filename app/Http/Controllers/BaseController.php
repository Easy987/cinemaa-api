<?php

namespace App\Http\Controllers;

use App\Http\Resources\Movie\BaseMovieResource;
use App\Http\Resources\Movie\MinimalLinkResource;
use App\Http\Resources\Movie\MovieResource;
use App\Models\Movie\BadLink;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BaseController extends Controller
{
    public function empty(Request $request)
    {
        return view('app_empty')->with('only_auth', false)->with('movie', null)->with('lang', 'hu');
    }

    public function index(Request $request, $uuid, $lang, $movie_id)
    {
        App::setLocale($lang);
        $movie = Movie::findOrFail($movie_id);

        if($movie->only_auth && !User::where('secret_uuid', $uuid)->exists()) {
            return view('app')
                ->with('only_auth', true)
                ->with('lang', $lang)
                ->with('uuid', $uuid);
        }

        $movieLinks = $movie->links()->where('status', '1')->orderBy('language_type_id', 'ASC')->orderBy('link_type_id', 'ASC')->orderBy('created_at', 'ASC')->get();

        if($movie->type === '1') {
            $links = [];
            $hasParts = false;

            if($movieLinks->count() > 0) {
                if($movieLinks->first()->part !== 0) {
                    $hasParts = true;

                    foreach($movieLinks as $link) {
                        if(!isset($links[$link->part])) {
                            $links[$link->part] = [];
                        }

                        $links[$link->part][] = (new MinimalLinkResource($link))->resolve();
                    }
                } else {
                    foreach($movieLinks as $link) {
                        if(!isset($links[$link->season])) {
                            $links[$link->season] = [];
                        }

                        if(!isset($links[$link->season][$link->episode])) {
                            $links[$link->season][$link->episode] = [];
                            ksort($links[$link->season]);
                        }

                        $links[$link->season][$link->episode][] = (new MinimalLinkResource($link))->resolve();
                    }
                }


                ksort($links);
            }
            return view('app')->with('only_auth', false)->with('user', Auth::guard('web')->user())->with('movie', (new BaseMovieResource($movie))->resolve())->with('lang', $lang)->with('uuid', $uuid)->with('parts', $hasParts)->with('links', $links);
        }

        return view('app')->with('only_auth', false)->with('user', Auth::guard('web')->user())->with('movie', (new BaseMovieResource($movie))->resolve())->with('lang', $lang)->with('uuid', $uuid)->with('links', MinimalLinkResource::collection($movieLinks)->resolve());
    }

    public function link(Request $request, $linkID)
    {
        $link = MovieLink::findOrFail($linkID);

        $link->movie->update(['watched_at' => now()]);
        views($link)->cooldown(10)->record();

        //return redirect('http://adf.ly/23301405/' . $link->link);
        return redirect($link->link);
    }

    public function report(Request $request, $uuid, $lang, $movie_id, $linkID)
    {
        $user = User::where('secret_uuid', $uuid)->firstOrFail();
        $link = MovieLink::findOrFail($linkID);

        $exists = BadLink::where('reportable_type', MovieLink::class)->where('reportable_id', $link->id)->where('user_id', $user->id)->where('movie_id', $link->movie->id)->exists();

        if($exists) {
            return response('Exists', 200);
        } else {
            BadLink::create([
                'reportable_type' => MovieLink::class,
                'reportable_id' => $link->id,
                'type' => 0,
                'user_id' => $user->id,
                'movie_id' => $link->movie->id,
                'message' => $request->get('message')
            ]);

            return response('Created', 202);
        }

    }

    public function share(Request $request, $lang, $slug, $year, $length)
    {
        $userAgent = $request->header('User-Agent');

        $movie = Movie::bySlug($slug)->where('year', $year)->where('length', $length)->firstOrFail();
        $title = $movie->titles()->where('lang', $lang)->first();
        if(!$title) {
            $title = $movie->titles->first();
        }

        $description = $movie->descriptions()->where('lang', $lang)->first();
        if(!$description) {
            $description = $movie->descriptions->first();
        }

        $image = $movie->poster ? route('cinema.photo', ['moviePhoto' => $movie->poster->id]) : '/img/covers/cover.jpg';

        $url = config('app.frontend_url') . '/' . $this->calculateMoviePrefix($movie->type, $lang) . '/' . $slug . '/' . $year;

        if (Str::contains($userAgent, 'Facebot') || Str::contains($userAgent, 'facebook')) {
            return view('share')
                ->with('movieTitle', $title->title)
                ->with('movieDescription', $description->description)
                ->with('movieImage', $image);
        }

        return redirect($url);
    }

    private function calculateMoviePrefix($type, $lang) {
        if($type === '0') { //Film
            if($lang === 'hu') {
                return 'film';
            } else {
                return 'movie';
            }
        } else { //Sorozat
            if($lang === 'hu') {
                return 'sorozat';
            } else {
                return 'serie';
            }
        }
    }

    public function view(Request $request, $slug, $year, $length = null)
    {
        $movie = null;

        if($length) {
            $movie = Movie::bySlug($slug)->where('year', $year)->where('length', $length)->exists();
            if($movie) {
                $movie = Movie::bySlug($slug)->where('year', $year)->where('length', $length)->first();
            }
        }

        if(!$movie) {
            $movie = Movie::bySlug($slug)->where('year', $year)->exists();
            if($movie) {
                $movie = Movie::bySlug($slug)->where('year', $year)->first();
            }
        }

        if(!$movie) {
            return redirect(config('app.frontend_url'));
        }

        $title = $movie->titles()->where('lang', 'hu')->first();
        if(!$title) {
            $title = $movie->titles->first();
        }

        $description = $movie->descriptions()->where('lang', 'hu')->first();
        if(!$description) {
            $description = $movie->descriptions->first();
        }

        $image = $movie->poster ? route('cinema.photo', ['moviePhoto' => $movie->poster->id]) : '/img/covers/cover.jpg';

        return view('view')
            //->with('movie', (new MovieResource($movie))->resolve())
            ->with('image', $image)
            ->with('year', $year)
            ->with('movieTitle', $title->title)
            ->with('movieDescription', $description->description ?? '')
            ->with('movieImage', $image);
    }
}
