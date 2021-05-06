<?php

namespace App\Http\Controllers;

use App\Http\Resources\Movie\MovieResource;
use App\Models\Movie\Actor;
use App\Models\Movie\Director;
use App\Models\Movie\Genre;
use App\Models\Movie\LanguageType;
use App\Models\Movie\LinkType;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieActor;
use App\Models\Movie\MovieDescription;
use App\Models\Movie\MovieDirector;
use App\Models\Movie\MovieGenre;
use App\Models\Movie\MovieLink;
use App\Models\Movie\MoviePhoto;
use App\Models\Movie\MovieTitle;
use App\Models\Movie\MovieVideo;
use App\Models\Movie\MovieWriter;
use App\Models\Movie\Writer;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    public function check(Request $request)
    {
        if($request->has('imdb') && $request->get('imdb') !== '') {
            $validator = Validator::make($request->all(), [
                'imdb' => ['sometimes', 'regex:/ev\d{7}\/\d{4}(-\d)?|(ch|co|ev|nm|tt)\d{7}/']
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $imdb = $request->get('imdb');

            preg_match("/tt\d{7,8}/", $imdb, $ids);

            if(count($ids) > 0) {
                $imdb = $ids[0];

                $movie = Movie::where('imdb_id', $imdb)->first();

                if($movie)
                {
                    return response(['data' => new MovieResource($movie), 'found' => true], 200);
                }

                try {
                    Movie::download($imdb, null, $request->user()->id);

                    $movie = Movie::where('imdb_id', $imdb)->firstOrFail();

                    return response(['data' => new MovieResource($movie), 'found' => false], 202);
                } catch (\Exception $e) {
                    return response('', 404);
                }
            }
        } else {
            return response('', 402);
        }
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_movie' => ['required'],
            'empty' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $newMovieData = $request->get('new_movie');
        $empty = $request->get('empty');

        // VALIDATION
        if(!isset($newMovieData['titles']) ^ count($newMovieData['titles']) < 2) {
            return response('', 301);
        }

        if(!isset($newMovieData['descriptions']) ^ count($newMovieData['descriptions']) < 2) {
            return response('', 301);
        }

        foreach($newMovieData['links'] as $link) {
            if(!isset($link['linkType']['id']) || !isset($link['languageType']['id'])) {
                return response('', 301);
            }
        }

        $videos = [];
        foreach($newMovieData['videos'] as $video) {
            $rx = '~
                  ^(?:https?://)?                           # Optional protocol
                   (?:www[.])?                              # Optional sub-domain
                   (?:youtube[.]com/watch[?]v=|youtu[.]be/) # Mandatory domain name (w/ query string in .com)
                   ([^&]{11})                               # Video id of 11 characters as capture group 1
                    ~x';

            $has_match = preg_match($rx, $video['youtube_id'], $matches);

            if(!$has_match) {
                return response('', 302);
            }

            $videos[] = $matches[1];
        }

        if($empty) {
            $movie = Movie::create([
                'status' => '0',
                'type' => (string)($newMovieData['type'] ?? 0),
                'year' => $newMovieData['year'] ?? 0,
                'length' => $newMovieData['length'] ?? 0,
                'imdb_rating' => 0,
                'imdb_votes' => 0,
                'user_id' => $request->user()->id,
                'porthu_id' => $newMovieData['porthu_id'] ?? null,
            ]);

            if(isset($newMovieData['poster'])) {
                $basePath = storage_path('images/movies/');

                $imageName = 'poster.png';

                File::makeDirectory($basePath . '/' . $movie->id, 0777, true, true);

                $photo = Image::make($newMovieData['poster'])
                    ->resize(270, 400)
                    ->encode('png',100);

                Storage::disk('movies')->put( $movie->id . '/' . $imageName, $photo);

                MoviePhoto::create([
                    'movie_id' => $movie->id,
                    'is_poster' => 1,
                    'extension' => 'png'
                ]);
            }
        } else {
            if($request->has('movie') && $request->get('movie') !== null) {
                $movieData = $request->get('movie');
                $movie = Movie::findOrFail($movieData['id']);
            } else {
                if(isset($newMovieData['porthu']) && ($newMovieData['porthu']) > 0 && $newMovieData['porthu'] !== '') {
                    try {
                        Movie::download($newMovieData['imdb'], $newMovieData['porthu'], $request->user()->id);

                        $movie = Movie::where('imdb_id', $newMovieData['imdb'])->firstOrFail();
                    } catch (\Exception $e) {
                        return response('', 305);
                    }
                } else {
                    return response('', 301);
                }
            }
        }

        if($empty) {
            foreach($newMovieData['titles'] as $key => $value) {
                $title = MovieTitle::where('movie_id', $movie->id)->where('lang', $key)->first();
                if($title) {
                    MovieTitle::where('movie_id', $movie->id)->where('lang', $key)->update(['title' => $value, 'slug' => Str::slug($value)]);
                } else {
                    MovieTitle::create([
                        'movie_id' => $movie->id,
                        'lang' => $key,
                        'title' => $value,
                        'slug' => Str::slug($value)
                    ]);
                }
            }

            foreach($newMovieData['descriptions'] as $key => $value) {
                $description = MovieDescription::where('movie_id', $movie->id)->where('lang', $key)->first();
                if($description) {
                    MovieDescription::where('movie_id', $movie->id)->where('lang', $key)->update(['description' => $value]);
                } else {
                    MovieDescription::create([
                        'movie_id' => $movie->id,
                        'lang' => $key,
                        'description' => $value,
                    ]);
                }
            }

            if(isset($newMovieData['genres'])) {
                MovieGenre::where('movie_id', $movie->id)->delete();
                foreach($newMovieData['genres'] as $key => $value) {
                    $genre = Genre::where('name', $value)->firstOrFail();
                    MovieGenre::create([
                        'movie_id' => $movie->id,
                        'genre_id' => $genre->id
                    ]);
                }
            }

            if(isset($newMovieData['actors'])) {
                MovieActor::where('movie_id', $movie->id)->delete();
                foreach($newMovieData['actors'] as $key => $value) {
                    $actor = Actor::where('name', $value)->firstOrFail();
                    MovieActor::create([
                        'movie_id' => $movie->id,
                        'actor_id' => $actor->id
                    ]);
                }
            }

            if(isset($newMovieData['directors'])) {
                MovieDirector::where('movie_id', $movie->id)->delete();
                foreach ($newMovieData['directors'] as $key => $value) {
                    $director = Director::where('name', $value)->firstOrFail();
                    MovieDirector::create([
                        'movie_id' => $movie->id,
                        'director_id' => $director->id
                    ]);
                }
            }

            if(isset($newMovieData['writers'])) {
                MovieWriter::where('movie_id', $movie->id)->delete();
                foreach ($newMovieData['writers'] as $key => $value) {
                    $writer = Writer::where('name', $value)->firstOrFail();
                    MovieWriter::create([
                        'movie_id' => $movie->id,
                        'writer_id' => $writer->id
                    ]);
                }
            }
        }

        foreach($newMovieData['links'] as $link) {
            if($link['link'] !== '') {
                $exists = MovieLink::where('link', $link['link'])->exists();

                if ($exists) {
                    return response('', 306);
                }
            }
        }

        foreach($newMovieData['links'] as $link) {
            $domain = parse_url(trim($link['link']));

            if(isset($domain['host'])) {
                $site = Site::where('url', $domain['host'])->exists();

                if($site) {
                    $linkSite = Site::where('url', $domain['host'])->first()->id;
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
                        'link_type_id' => $linkType->id,
                        'language_type_id' => $languageType->id,
                        'user_id' => $request->user()->id,
                        'status' => $request->user()->can('links.submit') ? '1' : '0',
                        'part' => $link['part'] ?? 0,
                        'season' => $link['season'] ?? 0,
                        'episode' => $link['episode'] ?? 0
                    ]);
                }
            } else {
                return response('', 300);
            }
        }

        foreach($videos as $video) {
            MovieVideo::create([
                'movie_id' => $movie->id,
                'youtube_id' => $video,
                'status' => $request->user()->can('links.submit') ? '1' : '0',
                'user_id' => $request->user()->id
            ]);
        }

        if($request->user()->can('links.submit')) {
            $movie->update(['status' => '1']);
        }

        if($movie->user->id === $request->user()->id) {

            $movie->update($newMovieData);

            MovieDescription::where('movie_id', $movie->id)->delete();
            foreach($newMovieData['descriptions'] as $lang => $description) {
                MovieDescription::create([
                    'movie_id' => $movie->id,
                    'description' => $description ?? '',
                    'lang' => $lang
                ]);
            }
        }

        return new MovieResource($movie);
    }
}
