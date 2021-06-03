<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BaseController;
use App\Jobs\CheckLink;
use App\Models\Movie\MovieLink;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['api', 'throttle:300,5'], 'prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/verify/{token}', [AuthController::class, 'verify']);
    Route::post('/forgot', [AuthController::class, 'forgot']);
    Route::post('/reset/{token}', [AuthController::class, 'reset']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me', [AuthController::class, 'update']);
    Route::post('/change_password', [AuthController::class, 'changePassword']);
    Route::post('/profile_picture', [AuthController::class, 'profilePicture']);
    Route::get('/users', [AuthController::class, 'users']);
    Route::get('/users/{username}', [AuthController::class, 'user']);
});

Route::group(['middleware' => ['api'], 'prefix' => 'movies'], function () {
    Route::get('info', [MovieController::class, 'info']);
    Route::get('top', [MovieController::class, 'top']);
    Route::get('popular', [MovieController::class, 'popular']);
    Route::get('recommends/premiers', [MovieController::class, 'recommendsPremiers']);
    Route::get('recommends/dvd', [MovieController::class, 'recommendsDVD']);
    Route::get('recommends/series', [MovieController::class, 'recommendsSeries']);
    Route::get('type/all', [MovieController::class, 'all']);
    Route::get('type/movies', [MovieController::class, 'movies']);
    Route::get('type/series', [MovieController::class, 'series']);
    Route::get('type/favourites', [MovieController::class, 'favourites']);
    Route::get('type/watcheds', [MovieController::class, 'watcheds']);
    Route::get('type/to_be_watcheds', [MovieController::class, 'toBeWatcheds']);
    Route::get('popularAll', [MovieController::class, 'popularAll']);
    Route::post('submitLink', [MovieController::class, 'submitLink']);
    Route::get('movie/{slug}/{year}/{length?}', [MovieController::class, 'movie']);


    Route::group(['middleware' => ['auth:api'], 'prefix' => 'upload'], function () {
        Route::get('check', [UploadController::class, 'check']);
        Route::post('', [UploadController::class, 'upload']);
    });

    Route::group(['middleware' => ['auth:api', 'throttle:300,5']], function () {
        Route::post('comment', [MovieController::class, 'comment']);
        Route::post('comment/like', [MovieController::class, 'commentLike']);
        Route::post('comment/delete', [MovieController::class, 'commentDelete']);
        Route::post('rate', [MovieController::class, 'rate']);
        Route::post('favourite', [MovieController::class, 'favourite']);
        Route::post('watched', [MovieController::class, 'watched']);
        Route::post('to_be_watched', [MovieController::class, 'toBeWatched']);
    });

    Route::group(['middleware' => ['auth:api', 'throttle:5,15']], function () {
        Route::post('report', [MovieController::class, 'report']);
    });
});

Route::group(['middleware' => ['api'], 'prefix' => 'photos'], function () {
    Route::get('{moviePhoto}', [PhotoController::class, 'photo'])->name('cinema.photo');
    Route::get('user/{userProfilePicture}', [PhotoController::class, 'userPhoto'])->name('cinema.userphoto');
});

Route::group(['middleware' => ['api', 'throttle:5,5']], function () {
    Route::post('contact', [ContactController::class, 'contact']);
});

Route::get('leaderboard', [GeneralController::class, 'leaderboard']);

Route::group(['prefix' => 'general','middleware' => ['api', 'auth:api']], function () {
    Route::get('chat/search', [ChatController::class, 'search']);
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('chat', [ChatController::class, 'index']);
        Route::get('chat/users', [ChatController::class, 'users']);
        Route::get('chat/{roomID}', [ChatController::class, 'messages']);
        Route::post('chat', [ChatController::class, 'send']);
        Route::delete('chat', [ChatController::class, 'kickUserFromGroup']);
        Route::post('chat/create_group', [ChatController::class, 'createGroup']);
        Route::post('chat/update_room', [ChatController::class, 'updateRoom']);
        Route::post('chat/unread', [ChatController::class, 'unread']);
        Route::delete('chat/group', [ChatController::class, 'deleteGroup']);
        Route::post('chat/reaction', [ChatController::class, 'sendReaction']);
        Route::post('chat/seen', [ChatController::class, 'seenMessage']);

    });

    Route::get('message_board', [GeneralController::class, 'messageBoardIndex']);
    Route::delete('message_board/{id}', [GeneralController::class, 'messageBoardDelete']);
    Route::get('requests', [GeneralController::class, 'requests']);
    Route::post('requests', [GeneralController::class, 'submitRequest']);
    Route::delete('requests', [GeneralController::class, 'deleteRequest']);
    Route::group(['middleware' => ['throttle:30,5']], function() {
        Route::post('message_board', [GeneralController::class, 'messageBoardStore']);
    });

    Route::get('leaderboard', [GeneralController::class, 'leaderboard']);
});

Route::group(['prefix' => 'forum','middleware' => ['api', 'auth:api']], function () {
    Route::get('discussions', [ForumController::class, 'discussions']);
    Route::get('discussions/{id}', [ForumController::class, 'topics']);
    Route::get('discussions/{id}/{topic}', [ForumController::class, 'topic']);
    Route::get('discussions/{id}/{topic}/posts', [ForumController::class, 'posts']);

    Route::post('post', [ForumController::class, 'post']);
    Route::post('post/like', [ForumController::class, 'postLike']);
    Route::post('post/delete', [ForumController::class, 'postDelete']);
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'admin'], function () {
    Route::get('info', [AdminController::class, 'info']);

    Route::get('users', [AdminController::class, 'users']);
    Route::delete('users', [AdminController::class, 'deleteUser']);
    Route::post('users/block', [AdminController::class, 'blockUser']);
    Route::get('users/{username}', [AdminController::class, 'user']);
    Route::post('users/{username}', [AdminController::class, 'saveUser']);

    Route::get('comments', [AdminController::class, 'comments']);
    Route::delete('comments', [AdminController::class, 'deleteComment']);

    Route::get('sites', [AdminController::class, 'sites']);
    Route::delete('sites', [AdminController::class, 'deleteSite']);
    Route::get('sites/{name}', [AdminController::class, 'site']);
    Route::post('sites/{name}', [AdminController::class, 'saveSite']);

    Route::get('movies', [AdminController::class, 'movies']);
    Route::delete('movies', [AdminController::class, 'deleteMovie']);
    Route::post('movies/block', [AdminController::class, 'blockMovie']);
    Route::post('movies/accept', [AdminController::class, 'acceptMovie']);
    Route::post('movies/refresh', [AdminController::class, 'refreshMovie']);
    Route::get('movies/{username}', [AdminController::class, 'movie']);
    Route::post('movies/{username}', [AdminController::class, 'saveMovie']);

    Route::delete('videos', [AdminController::class, 'deleteVideo']);

    Route::get('links', [AdminController::class, 'links']);
    Route::delete('links', [AdminController::class, 'deleteLink']);
    Route::delete('linksMultiple', [AdminController::class, 'deleteLinks']);
    Route::post('links/block', [AdminController::class, 'blockLink']);
    Route::post('links/accept', [AdminController::class, 'acceptLink']);
    Route::get('links/{id}', [AdminController::class, 'link']);
    Route::post('links/{id}', [AdminController::class, 'saveLink']);

    Route::get('preliminaries', [AdminController::class, 'preliminaries']);
    Route::delete('preliminaries', [AdminController::class, 'deletePreliminary']);
    Route::post('preliminaries/accept', [AdminController::class, 'acceptPreliminary']);

    Route::get('reports', [AdminController::class, 'reports']);
    Route::delete('reports', [AdminController::class, 'deleteReport']);

    Route::get('forum/discussions', [AdminController::class, 'forumDiscussions']);
    Route::get('forum/discussions/{id}', [AdminController::class, 'forumDiscussion']);
    Route::get('forum/topics/{id}', [AdminController::class, 'forumTopic']);
    Route::get('forum/{id}/topics', [AdminController::class, 'forumTopics']);
    Route::post('forum/save', [AdminController::class, 'forumSave']);
    Route::delete('forum/delete', [AdminController::class, 'forumDelete']);
});

Route::get('nBcYyMVjB8', function() {
    /*$links = MovieLink::all();

    $count = 0;

    foreach($links as $link) {
        if($link->site_id !== null) {
            $site = Site::where('id', $link->site_id)->exists();

            if(!$site) {
                $link->delete();
                $count++;
            }
        }
    }

    dd($count);*/

    /*$links = \App\Models\Movie\MovieLink::where('site_id', null)->get();
    foreach($links as $link) {

        $url = $link['link'];
        $domain = parse_url(trim($url));
        if(isset($domain['host'])) {
            $domain = Site::getDomain($domain['host'], false);

            $site = Site::where('url', $domain)->exists();

            if ($site) {
                $linkSite = Site::where('url', $domain)->first()->id;

                $link->update(['site_id' => $linkSite]);
            }
        }
    }
    exit();*/

    /*$movie = \App\Models\Movie\Movie::inRandomOrder()->first();
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

    \Berkayk\OneSignal\OneSignalFacade::addParams($params)->sendNotificationUsingTags(
        "📢 Új premier érkezett!\n\n⭐".($movieTitle->title)." - ".($movie->year)."⭐\nMűfaj: ".(implode(', ', $genres))."\n" . ($movie->getDescription()->description),
        array(
            ["field" => "tag", "key" => "username", "relation" => "=", "value" => 'Easy987'],
        )
    );

    dd(1);
    $movies = DB::connection('old_mysql')->table('movies')->where('imdb_id', '!=', null)->get()->take(100);

    foreach($movies as $movie) {
        $ownMovie = \App\Models\Movie\Movie::where('imdb_id', $movie->imdb_id)->first();

        $porthu = $movie->porthu !== null && $movie->porthu !== '' ? $movie->porthu : null;

        if($ownMovie && $ownMovie->user_id !== null) {
            dispatch(new \App\Jobs\DownloadMovie($movie->imdb_id, $porthu, $ownMovie->user_id));
        } else {
            dispatch(new \App\Jobs\DownloadMovie($movie->imdb_id, $porthu, null));
        }
    }
    dd(1);

    $links = MovieLink::where('status', '!=', '3')->whereHas('site', function(\Illuminate\Database\Eloquent\Builder $subQuery) {
        $subQuery->whereNotIn('name', ['STREAMZZ', 'STREAMCRYPT', 'WOLFSTREAM']);
    })->limit(100)->orderBy('created_at', 'DESC')->get();

    foreach($links as $link) {
        dispatch(new CheckLink($link->id, $link->link))->onQueue('low');
    }

    dd($links);*/
});
/*
Route::get('nBcYyMVjB8', function(\Illuminate\Support\Facades\Request $request) {
    if(request()->has('test')) {
        $links = \App\Models\Movie\MovieLink::where('site_id', null)->get();
        $sites = \App\Models\Site::all();


        foreach($links as $link) {
            $goodSites = $sites->filter(function($site) use ($link) {
                return \Illuminate\Support\Str::contains($link->link, $site->url);
            });

            if($goodSites->count() > 0) {
                $goodSite = $goodSites->first();

                $link->update(['site_id' => $goodSite->id]);
            }

        }
        exit();
    }


    if(!request()->has('all')) {
        \App\Models\Movie\Movie::download('tt1190634', null, null);
        dd(1);
    } else {
        $movies = DB::connection('old_mysql')->table('movies')->where('imdb_id', '!=', null)->get()->take(100);

        foreach($movies as $movie) {
            $ownMovie = \App\Models\Movie\Movie::where('imdb_id', $movie->imdb_id)->first();

            $porthu = $movie->porthu !== null && $movie->porthu !== '' ? $movie->porthu : null;

            if($ownMovie && $ownMovie->user_id !== null) {
                dispatch(new \App\Jobs\DownloadMovie($movie->imdb_id, $porthu, $ownMovie->user_id));
            } else {
                dispatch(new \App\Jobs\DownloadMovie($movie->imdb_id, $porthu, null));
            }
        }
        //\App\Models\Movie\Movie::download($id, 2087, \App\Models\User::first()->id);
        dd(1);
    }
});
*/
