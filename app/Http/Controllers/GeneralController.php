<?php

namespace App\Http\Controllers;

use App\Events\MessageBoardMessageSent;
use App\Events\RequestReceived;
use App\Http\Resources\MessageBoardResource;
use App\Http\Resources\RequestResource;
use App\Models\Forum\ForumPost;
use App\Models\MessageBoardChat;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieComment;
use App\Models\Movie\MovieLink;
use App\Models\Movie\MovieRating;
use App\Models\Movie\MovieView;
use App\Models\Movie\MovieWatched;
use App\Models\RequestNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function messageBoardIndex(Request $request)
    {
        return MessageBoardResource::collection(MessageBoardChat::orderBy('created_at', 'DESC')->limit(300)->get());
    }

    public function messageBoardStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $message = MessageBoardChat::create([
            'user_id' => $request->user()->id,
            'text' => $request->get('message')
        ]);

        broadcast(new MessageBoardMessageSent(new MessageBoardResource($message)))->toOthers();

        return $this->messageBoardIndex($request);
    }

    public function messageBoardDelete(Request $request, $id)
    {
        $message = MessageBoardChat::findOrFail($id);

        if($request->user()->can('admin.sites.delete')) { // Tulaj
            $message->delete();
        }

        return $this->messageBoardIndex($request);
    }

    public function requests(Request $request)
    {
        if($request->user()->can('links.submit')) { // Összes
            $requests = \App\Models\Request::query()->orderBy('created_at', 'desc');
        } else {
            $requests = \App\Models\Request::query()->where('user_id', $request->user()->id)->orderBy('created_at', 'desc');
        }

        RequestNotification::where('user_id', $request->user()->id)->update(['seen' => 1]);

        return RequestResource::collection($requests->paginate(24));
    }

    public function submitRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $requestData = $request->get('request');

        \App\Models\Request::create([
            'user_id' => $request->user()->id,
            'title' => $requestData['title'],
            'body' => $requestData['body']
        ]);

        $users = User::permission('links.submit')->get();

        foreach($users as $user) {
            RequestNotification::create([
                'user_id' => $user->id,
            ]);

            broadcast(new RequestReceived($user));
        }
    }

    public function deleteRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $requestData = \App\Models\Request::findOrFail($request->get('id'));

        if($request->user()->can('links.submit')) {
            $requestData->delete();
        }
    }

    public function leaderboard(Request $request)
    {
        $data = Cache::remember('leaderboard', /*60*60*24*/ 1, function() use ($request) {
            $data = [];

            // feltöltött adatlapok darabszáma
            $data['uploaded_movies'] = Movie::has('user')->with('user')->withTrashed()->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // feltöltött linkek darabszáma
            $data['uploaded_links'] = MovieLink::has('user')->with('user')->withTrashed()->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // adatlap értékelések darabszáma
            $data['movies_rated'] = MovieRating::has('user')->with('user')->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // megnézett adatlapok darabszáma
            $data['watched_movies'] = MovieWatched::has('user')->with('user')->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // megtekintett adatlapok darabszáma
            $data['viewed_movies'] = MovieView::has('user')->with('user')->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // kommentek darabszáma
            $data['comments'] = MovieComment::has('user')->with('user')->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // üzenőfal kommentek darabszáma
            $data['message_board_comments'] = MessageBoardChat::has('user')->with('user')->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // posztok darabszáma
            $data['posts'] = ForumPost::has('user')->with('user')->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();

            // beküldött kérések darabszáma
            $data['sent_requests'] = \App\Models\Request::has('user')->with('user')->withTrashed()->groupBy('user_id')->select('user_id', DB::raw('COUNT(user_id) as count'))->where('user_id', '!=', null)->orderByDesc(DB::raw('COUNT(user_id)'))->get();


            foreach($data as $key => $category) {
                //$category = $category->sortByDesc('count');

                $ownData = $category->search(function($category) use ($request) {
                    return $category->user_id === $request->user()->id;
                });

                if($ownData !== false) {
                    $ownData = [
                        'count' => $category[$ownData]['count'],
                        'order' => $ownData
                    ];
                } else {
                    $ownData = [];
                }


                $statData = [
                    'users' => [],
                    'own' => count($ownData) > 0 ? [
                        'username' => $request->user()->username,
                        'score' => $ownData['count'],
                        'picture' => $request->user()->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $request->user()->profile_picture->id]) : '/img/user.svg',
                        'order' => $ownData['order']
                    ] : []
                ];

                foreach($category as $index => $user) {
                    if(count($statData['users']) < 50) {
                        $statData['users'][] = [
                            'username' => $user->user->username ?? '',
                            'picture' => $user->user->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $user->user->profile_picture->id]) : '/img/user.svg',
                            'score' => $user->count,
                            'order' => $index,
                        ];
                    }
                }

                $statData['top'] = array_slice($statData['users'], 0, 3);
                $statData['users'] = array_slice($statData['users'], 3);

                $data[$key] = $statData;
            }

            return $data;
        });

        return response()->json($data);
    }
}
