<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminUserResource;
use App\Http\Resources\ForumDiscussionResource;
use App\Http\Resources\ForumTopicResource;
use App\Http\Resources\Movie\AdminMinimalLinkResource;
use App\Http\Resources\Movie\AdminMinimalLinksResource;
use App\Http\Resources\Movie\AdminMovieMinimalResource;
use App\Http\Resources\Movie\AdminMovieResource;
use App\Http\Resources\Movie\BadLinkResource;
use App\Http\Resources\Movie\CommentResource;
use App\Http\Resources\Movie\ItemResource;
use App\Http\Resources\Movie\LinkResource;
use App\Http\Resources\Movie\MinimalLinkResource;
use App\Http\Resources\Movie\MovieVideoResource;
use App\Http\Resources\SiteResource;
use App\Http\Resources\UserResource;
use App\Models\Forum\ForumDiscussion;
use App\Models\Forum\ForumTopic;
use App\Models\Movie\Actor;
use App\Models\Movie\BadLink;
use App\Models\Movie\Director;
use App\Models\Movie\Genre;
use App\Models\Movie\LanguageType;
use App\Models\Movie\LinkType;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieActor;
use App\Models\Movie\MovieComment;
use App\Models\Movie\MovieDescription;
use App\Models\Movie\MovieDirector;
use App\Models\Movie\MovieGenre;
use App\Models\Movie\MovieLink;
use App\Models\Movie\MoviePhoto;
use App\Models\Movie\MovieTitle;
use App\Models\Movie\MovieVideo;
use App\Models\Movie\MovieWriter;
use App\Models\Movie\Writer;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Berkayk\OneSignal\OneSignalFacade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use League\CommonMark\Inline\Element\Link;

class AdminController extends Controller
{
    public function info(Request $request)
    {
        if($request->user()->can('admin.index')) {
            return response([
                'movies_count' => Movie::count(),
                'movies_views' => views(Movie::class)->count(),
                'comments_count' => MovieComment::count(),
                'users_count' => User::count(),
            ], 200);
        }
    }

    public function users(Request $request)
    {
        if($request->user()->can('admin.users.index')) {
            $users = User::query();

            if($request->hasAny(User::$filters)){
                $filters = $request->only(User::$filters);
                foreach($filters as $type => $filter) {
                    $users = $users->filter($type, json_decode($filter, true));
                }
            }

            $paginate = $users->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $users->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return AdminUserResource::collection($paginate);
        }

        return response('', 404);
    }

    public function user(Request $request, $username)
    {
        if($request->user()->can('admin.users.index')) {
            $user = User::where('username', $username)->firstOrFail();

            return new UserResource($user);
        }

        return response('', 404);
    }

    public function saveUser(Request $request, $username)
    {
        if($request->user()->can('admin.users.index')) {
            $userData = $request->get('user');
            $userData['status'] = (string)$userData['status'];

            $user = User::findOrFail($userData['id']);
            $user->update($userData);

            if(isset($userData['role'])) {
                $roleModel = Role::findByName($userData['role']);

                $user->syncRoles([$roleModel]);
            }

            return new UserResource($user);
        }

        return response('', 404);
    }

    public function deleteUser(Request $request)
    {
        if($request->user()->can('admin.users.index')) {
            $validator = Validator::make($request->all(), [
                'user' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = User::findOrFail($request->get('user'));

            $user->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function blockUser(Request $request)
    {
        if($request->user()->can('admin.users.index')) {
            $validator = Validator::make($request->all(), [
                'user' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $userID = $request->get('user');
            $user = User::findOrFail($userID);

            if($user->status === '2' && $user->email_verified_at !== null) {
                $user->status = '1';
            } else if($user->status === '2' && $user->email_verified_at === null) {
                $user->status = '0';
            } else if($user->status < '2') {
                $user->status = '2';
            }
            $user->save();

            return new UserResource($user);
        }

        return response('', 404);
    }

    public function comments(Request $request)
    {
        if($request->user()->can('admin.comments.index')) {
            $comments = MovieComment::query()->orderBy('created_at', 'DESC');

            $paginate = $comments->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $comments->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return CommentResource::collection($paginate);
        }

        return response('', 404);
    }

    public function deleteComment(Request $request)
    {
        if($request->user()->can('admin.comments.index')) {
            $validator = Validator::make($request->all(), [
                'comment' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $comment = MovieComment::findOrFail($request->get('comment'));

            $comment->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function sites(Request $request)
    {
        if($request->user()->can('admin.sites.index')) {
            $sites = Site::query();

            $paginate = $sites->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $sites->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return SiteResource::collection($paginate);
        }

        return response('', 404);
    }

    public function deleteSite(Request $request)
    {
        if($request->user()->can('admin.sites.delete')) {
            $validator = Validator::make($request->all(), [
                'site' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $site = Site::findOrFail($request->get('site'));

            MovieLink::where('site_id', $site->id)->delete();

            $site->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function site(Request $request, $name)
    {
        if($request->user()->can('admin.sites.index')) {
            $site = Site::where('name', $name)->firstOrFail();

            return new SiteResource($site);
        }

        return response('', 404);
    }

    public function saveSite(Request $request, $name)
    {
        if($request->user()->can('admin.sites.index')) {
            $data = $request->get('site');

            if(isset($data['id'])) {
                $site = Site::findOrFail($data['id']);
                $site->update($data);
            } else {
                $site = Site::create($data);
            }

            return new SiteResource($site);
        }

        return response('', 404);
    }

    public function movies(Request $request)
    {
        if($request->user()->can('admin.index')) {

            $movies = Movie::query()->with(['titles', 'poster', 'genres', 'ratings', 'descriptions', 'writers', 'directors', 'videos']);

            if(!$request->user()->can('admin.movies.index') && !$request->has('empty_links')) {
                $movies = $movies->whereHas('user',  function(Builder $subQuery) use ($request) {
                    return $subQuery->where('id', $request->user()->id);
                });
            }

            if($request->hasAny(Movie::$filters)){
                $filters = $request->only(Movie::$filters);
                foreach($filters as $type => $filter) {
                    $movies = $movies->filter($type, json_decode($filter, true));
                }
            }

            $paginate = $movies->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $movies->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return AdminMovieMinimalResource::collection($paginate);
        }

        return response('', 404);
    }

    public function movie(Request $request, $id)
    {
        if($request->user()->can('admin.index') && $this->hasAccessToMovie($id, $request->user())) {
            $movie = Movie::findOrFail($id);

            return new AdminMovieResource($movie);
        }

        return response('', 404);
    }

    public function saveMovie(Request $request, $id)
    {
        if($request->user()->can('admin.index')) {

            if($this->hasAccessToMovie($id, $request->user())) {
                $movie = Movie::findOrFail($id);

                $movieData = $request->get('movie');

                if(!$request->user()->can('admin.sites.delete')) {
                    unset($movieData['only_auth']);
                }

                $movieData['type'] = (string)$movieData['type'];

                if(isset($movieData['poster'])) {
                    $basePath = storage_path('images/movies/');

                    $imageName = 'poster.png';

                    File::makeDirectory($basePath . '/' . $movie->id, 0777, true, true);

                    $existingPoster = MoviePhoto::where('movie_id', $movie->id)->where('is_poster', 1)->first();

                    if($existingPoster) {
                        Storage::disk('movies')->delete($movie->id . '/' . 'poster.' . $existingPoster->extension);
                        $existingPoster->delete();
                    }

                    $photo = Image::make($movieData['poster'])
                        ->resize(270, 400)
                        ->encode('png',100);

                    Storage::disk('movies')->put( $movie->id . '/' . $imageName, $photo);

                    MoviePhoto::create([
                        'movie_id' => $movie->id,
                        'is_poster' => 1,
                        'extension' => 'png'
                    ]);
                }

                foreach($movieData['titles'] as $key => $value) {
                    $title = MovieTitle::where('movie_id', $movieData['id'])->where('lang', $key)->first();
                    if($title) {
                        MovieTitle::where('movie_id', $movieData['id'])->where('lang', $key)->update(['title' => $value, 'slug' => Str::slug($value)]);
                    } else {
                        MovieTitle::create([
                            'movie_id' => $movieData['id'],
                            'lang' => $key,
                            'title' => $value,
                            'slug' => Str::slug($value)
                        ]);
                    }
                }

                foreach($movieData['descriptions'] as $key => $value) {
                    $description = MovieDescription::where('movie_id', $movieData['id'])->where('lang', $key)->first();
                    if($description) {
                        MovieDescription::where('movie_id', $movieData['id'])->where('lang', $key)->update(['description' => $value]);
                    } else {
                        MovieDescription::create([
                            'movie_id' => $movieData['id'],
                            'lang' => $key,
                            'description' => $value,
                        ]);
                    }
                }

                MovieGenre::where('movie_id', $movieData['id'])->delete();
                foreach($movieData['genres'] as $key => $value) {
                    $genre = Genre::where('name', $value)->firstOrFail();
                    MovieGenre::create([
                        'movie_id' => $movieData['id'],
                        'genre_id' => $genre->id
                    ]);
                }

                MovieActor::where('movie_id', $movieData['id'])->delete();
                foreach($movieData['actors'] as $key => $value) {
                    $actor = Actor::where('name', $value)->firstOrFail();
                    MovieActor::create([
                        'movie_id' => $movieData['id'],
                        'actor_id' => $actor->id
                    ]);
                }

                MovieDirector::where('movie_id', $movieData['id'])->delete();
                foreach($movieData['directors'] as $key => $value) {
                    $director = Director::where('name', $value)->firstOrFail();
                    MovieDirector::create([
                        'movie_id' => $movieData['id'],
                        'director_id' => $director->id
                    ]);
                }

                MovieWriter::where('movie_id', $movieData['id'])->delete();
                foreach($movieData['writers'] as $key => $value) {
                    $writer = Writer::where('name', $value)->firstOrFail();
                    MovieWriter::create([
                        'movie_id' => $movieData['id'],
                        'writer_id' => $writer->id
                    ]);
                }


                MovieVideo::where('movie_id', $movieData['id'])->delete();
                foreach($movieData['videos'] as $video) {
                    MovieVideo::create([
                        'movie_id' => $movieData['id'],
                        'youtube_id' => $video['youtube_id'],
                        'status' => (string)$video['status']
                    ]);
                }

                foreach($movieData['links'] as $link) {
                    MovieLink::updateOrCreate([
                        'id' => $link['id'],
                        'movie_id' => $movieData['id'],
                    ], [
                        'movie_id' => $movieData['id'],
                        'status' => (string)$link['status'],
                        'site_id' => $link['site'] ? $link['site']['id'] : null,
                        'link_type_id' => $link['linkType'] ? $link['linkType']['id'] : null,
                        'language_type_id' => $link['languageType'] ? $link['languageType']['id'] : null,
                        'user_id' => $link['user'] ? $link['user']['id'] : null,
                        'link' => $link['link'],
                        'part' => $link['part'] ?? 0,
                        'season' => $link['season'] ?? 0,
                        'episode' => $link['episode'] ?? 0
                    ]);
                }

                $movieData['status'] = (string)$movieData['status'];

                if($movie->is_premier === 0 && $movieData['is_premier']) {
                    $movieData['premier_date'] = now();

                    if(config('app.env') !== 'local') {
                        $genres = $movie->genres->take(3)->map(function($genre) {
                            return \Illuminate\Support\Facades\Lang::get('base.genres.' . $genre->name);
                        })->toArray();

                        $movieTitle = $movie->getTitle();

                        $poster = null;
                        if($movie->poster()->exists()) {
                            $poster = $movie->poster()->first();
                        }

                        $params = [];
                        $params['url'] = 'https://cinemaa.cc/film/' . $movieTitle->slug . '/' . $movie->year . '/' . $movie->length;
                        $params['web_push_topic'] = $movieTitle->slug;

                        if($poster) {
                            $keys = ['small_icon', 'large_icon',
                                'chrome_web_icon', 'chrome_web_badge'];

                            foreach($keys as $key) {
                                $params[$key] = 'https://cinemaa.cc/api/photos/' . $poster->id;
                            }
                        }

                        OneSignalFacade::addParams($params)
                            ->sendNotificationToAll(
                            "📢 Új premier érkezett!\n\n⭐".($movieTitle->title)." - ".($movie->year)."⭐\nMűfaj: ".(implode(', ', $genres))."\n" . ($movie->getDescription()->description)
                            );
                    }
                }

                $movie->update($movieData);

                return new AdminMovieResource($movie);
            }
        }

        return response('', 404);
    }

    public function deleteMovie(Request $request)
    {
        if($request->user()->can('admin.movies.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $movieID = $request->get('id');
            if($this->hasAccessToMovie($movieID, $request->user())) {
                $movie = Movie::findOrFail($movieID);

                //File::deleteDirectory(storage_path() . '/images/movies/' . $movie->id);

                $movie->delete();

                return response('', 202);
            }
        }

        return response('', 404);
    }

    public function blockMovie(Request $request)
    {
        if($request->user()->can('admin.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $movieID = $request->get('id');

            if($this->hasAccessToMovie($movieID, $request->user())) {
                $movie = Movie::findOrFail($movieID);

                if($movie->status === '2') {
                    $movie->status = '0';
                } else {
                    $movie->status = '2';
                }

                $movie->save();

                return new AdminMovieResource($movie);
            }
        }

        return response('', 404);
    }

    public function acceptMovie(Request $request)
    {
        if($request->user()->can('admin.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $movieID = $request->get('id');

            if($this->hasAccessToMovie($movieID, $request->user())) {
                $movie = Movie::findOrFail($movieID);

                $movie->status = '1';
                $movie->save();

                return new AdminMovieResource($movie);
            }
        }

        return response('', 404);
    }

    private function hasAccessToMovie($id, $user) {
        if($user->can('admin.movies.index')) {
            return true;
        }

        $movie = Movie::findOrFail($id);
        if($movie->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function deleteVideo(Request $request)
    {
        if($request->user()->can('admin.movies.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
                'movie_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $videoID = $request->get('id');
            $movieID = $request->get('movie_id');
            MovieVideo::where('youtube_id', $videoID)->where('movie_id', $movieID)->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function links(Request $request)
    {
        if($request->user()->can('admin.index')) {
            $links = MovieLink::query();

            if($request->user()->hasRole('uploader')) {
                $links = $links->whereHas('user',  function(Builder $subQuery) use ($request) {
                    return $subQuery->where('id', $request->user()->id);
                });
            }

            if($request->hasAny(MovieLink::$filters)){
                $filters = $request->only(MovieLink::$filters);
                foreach($filters as $type => $filter) {
                    $links = $links->filter($type, json_decode($filter, true));
                }
            }

            $paginate = $links->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $links->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return AdminMinimalLinksResource::collection($paginate);
        }

        return response('', 404);
    }

    public function link(Request $request, $id)
    {
        if($request->user()->can('admin.index')) {
            $link = MovieLink::findOrFail($id);

            return new LinkResource($link);
        }

        return response('', 404);
    }

    public function saveLink(Request $request, $id)
    {
        if($request->user()->can('admin.index')) {
            $link = MovieLink::findOrFail($id);

            $linkData = $request->get('link');
            $linkData['status'] = (string)$linkData['status'];
            $link->update($linkData);

            return new LinkResource($link);
        }

        return response('', 404);
    }

    public function deleteLink(Request $request)
    {
        if($request->user()->can('links.submit')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $linkID = $request->get('id');
            $link = MovieLink::findOrFail($linkID);

            if(!$request->user()->can('admin.movies.index') && $link->user_id !== $request->user()->id) {
                return response('', 404);
            }

            $link->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function deleteLinks(Request $request)
    {
        if($request->user()->can('links.submit')) {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $linkIDs = json_decode($request->get('ids'), true);

            foreach($linkIDs as $linkID) {
                if($linkID['value']) {
                    $link = MovieLink::findOrFail($linkID['id']);

                    if(!$request->user()->can('admin.movies.index') && $link->user_id !== $request->user()->id) {
                        return response('', 404);
                    }

                    $link->delete();
                }
            }

            return response('', 202);
        }

        return response('', 404);
    }

    public function blockLink(Request $request)
    {
        if($request->user()->can('admin.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $linkID = $request->get('id');
            $link = MovieLink::findOrFail($linkID);

            if($link->status === '2') {
                $link->status = '0';
            } else {
                $link->status = '2';
            }

            $link->save();

            return new LinkResource($link);
        }

        return response('', 404);
    }

    public function acceptLink(Request $request)
    {
        if($request->user()->can('admin.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $linkID = $request->get('id');
            $link = MovieLink::findOrFail($linkID);

            $link->status = '1';
            $link->save();

            return new LinkResource($link);
        }

        return response('', 404);
    }

    public function preliminaries(Request $request)
    {
        if($request->user()->can('admin.preliminaries.index')) {

            $movieVideos = MovieVideo::query()->where('status', '=', '0');
            $hasFilters = false;

            if($request->hasAny(MovieVideo::$filters)){
                $hasFilters = true;
                $filters = $request->only(MovieVideo::$filters);
                foreach($filters as $type => $filter) {
                    $movieVideos = $movieVideos->filter($type, json_decode($filter, true));
                }
            }

            $paginate = $movieVideos->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $movieVideos->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return MovieVideoResource::collection($paginate);
        }

        return response('', 404);
    }

    public function deletePreliminary(Request $request)
    {
        if($request->user()->can('admin.preliminaries.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $movieVideoID = $request->get('id');
            $movieVideo = MovieVideo::findOrFail($movieVideoID);
            $movieVideo->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function acceptPreliminary(Request $request)
    {
        if($request->user()->can('admin.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $movieVideoID = $request->get('id');
            $movieVideo = MovieVideo::findOrFail($movieVideoID);

            $movieVideo->status = '1';
            $movieVideo->save();

            return new MovieVideoResource($movieVideo);
        }

        return response('', 404);
    }

    public function reports(Request $request)
    {
        if($request->user()->can('admin.reports.index')) {

            $badLinks = BadLink::query()->with('reportable');

            $paginate = $badLinks->paginate(30);
            if($request->get('page') > $paginate->lastPage()) {
                $paginate = $badLinks->paginate(30, ['*'], 'page', $paginate->lastPage());
            }

            return BadLinkResource::collection($paginate);
        }

        return response('', 404);
    }

    public function deleteReport(Request $request)
    {
        if($request->user()->can('admin.reports.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
                'remove' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $badLinkID = $request->get('id');
            $remove = $request->get('remove');

            $badLink = BadLink::findOrFail($badLinkID);

            if($remove === 'true') {
                if($badLink->reportable_type === MovieVideo::class) {
                    MovieVideo::where('id', $badLink->reportable->id)->delete();
                } else {
                    MovieLink::where('id', $badLink->reportable->id)->delete();
                }
            }

            $badLink->delete();

            return response('', 202);
        }

        return response('', 404);
    }

    public function refreshMovie(Request $request) {
        if($request->user()->can('admin.movies.index')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $movie = Movie::findOrFail($request->get('id'));

            Movie::download($movie->imdb_id, $movie->porthu_id, $movie->user_id);

            $movie = Movie::findOrFail($request->get('id'));

            return new AdminMovieResource($movie);
        }
    }

    public function forumDiscussions(Request $request)
    {
        if($request->user()->can('admin.forums.index')) {
            $discussions = ForumDiscussion::query();

            $paginate = $discussions->paginate(100);
            if ($request->get('page') > $paginate->lastPage()) {
                $paginate = $discussions->paginate(100, ['*'], 'page', $paginate->lastPage());
            }

            return ForumDiscussionResource::collection($paginate);
        }
    }

    public function forumTopics(Request $request, $id)
    {
        if($request->user()->can('admin.forums.index')) {
            $discussion = ForumDiscussion::findOrFail($id);

            $topics = $discussion->topics();

            $paginate = $topics->paginate(100);
            if ($request->get('page') > $paginate->lastPage()) {
                $paginate = $topics->paginate(100, ['*'], 'page', $paginate->lastPage());
            }

            return ForumTopicResource::collection($paginate);
        }
    }

    public function forumDiscussion(Request $request, $id)
    {
        if($request->user()->can('admin.forums.index')) {

            $discussion = ForumDiscussion::findOrFail($id);

            return new ForumDiscussionResource($discussion);
        }
    }

    public function forumTopic(Request $request, $id)
    {
        if($request->user()->can('admin.forums.index')) {

            $topic = ForumTopic::findOrFail($id);

            return new ForumTopicResource($topic);
        }
    }

    public function forumDelete(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type');

        if($type === 'true') {
            $discussion = ForumDiscussion::where('id', $id)->firstOrFail();
        } else {
            $discussion = ForumTopic::where('id', $id)->firstOrFail();
        }

        if($request->user()->can('admin.forums.delete')) {
            $discussion->delete();
        } else {
            return response(null , 403);
        }

        return response(null,200);
    }

    public function forumSave(Request $request)
    {
        $data = $request->get('discussion');
        $id = $request->get('id');
        $type = $request->get('type');

        if($request->user()->can('admin.forums.create')) {
            if(isset($data['id'])) { //Editing
                if($type === 'discussion') {
                    ForumDiscussion::where('id', $id)->update(['name' => $data['name'], 'description' => $data['description']]);
                } else {
                    ForumTopic::where('id', $id)->update(['name' => $data['name'], 'description' => $data['description']]);
                }
            } else { // Saving
                if($type === 'discussion') {
                    ForumDiscussion::create([
                        'name' => $data['name'],
                        'description' => $data['description']
                    ]);
                } else {
                    ForumTopic::create([
                        'discussion_id' => $id,
                        'name' => $data['name'],
                        'description' => $data['description']
                    ]);
                }
            }
        } else {
            return response(null , 403);
        }
    }
}
