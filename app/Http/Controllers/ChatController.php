<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\ChatMessageUpdated;
use App\Events\ChatRoomCreated;
use App\Http\Resources\ChatLastMessageResource;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatRoomResource;
use App\Http\Resources\ChatUserResource;
use App\Http\Resources\ChatUserSearchResource;
use App\Http\Resources\UserMinimalResource;
use App\Jobs\UpdateAllMessageSeen;
use App\Jobs\UpdateMessageSeen;
use App\Models\ChatRoom\ChatRoom;
use App\Models\ChatRoom\ChatRoomMessage;
use App\Models\ChatRoom\ChatRoomMessageReaction;
use App\Models\ChatRoom\ChatRoomMessageSeen;
use App\Models\ChatRoom\ChatRoomUser;
use App\Models\RequestNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $chatRooms = ChatRoom::whereHas('users', function(Builder $query) use ($request) {
            $query->where('id', $request->user()->id);
        })->get();

        return ChatRoomResource::collection($chatRooms);
    }

    public function seenMessage(Request $request) {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $message = ChatRoomMessage::findOrFail($request->get('message_id'));

        if($message->user_id !== $request->user()->id) {
            ChatRoomMessageSeen::firstOrCreate(['user_id' => $request->user()->id, 'message_id' => $message->id]);
        }

        $message = ChatRoomMessage::findOrFail($request->get('message_id'));

        $roomUsers = $message->room->users()->where('last_activity_at', '>', Carbon::now()->subDays(2))->get();
        foreach($roomUsers as $roomUser) {
            broadcast(new ChatMessageUpdated($roomUser->id, new ChatMessageResource($message, $request->user()->id)));
        }
    }

    public function deleteGroup(Request $request) {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $roomID = $request->get('room_id');

        $room = ChatRoom::findOrFail($roomID);

        if($room->user->id === $request->user()->id) {
            foreach($room->users as $roomUser) {
                broadcast(new ChatRoomCreated($roomUser, $room))->toOthers();
            }
            $room->delete();

            return response('', 200);
        }

        return response('', 400);
    }

    public function kickUserFromGroup(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid',
            'room_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $roomID = $request->get('room_id');
        $userID = $request->get('user_id');

        $room = ChatRoom::findOrFail($roomID);
        $user = User::findOrFail($userID);

        if($room->user->id === $request->user()->id) {
            foreach($room->users as $roomUser) {
                broadcast(new ChatRoomCreated($roomUser, $room))->toOthers();
            }

            ChatRoomUser::where('room_id', $roomID)->where('user_id', $user->id)->delete();

            return response('', 200);
        }
    }

    public function messages(Request $request, $roomID)
    {
        $room = ChatRoom::with('users')->find($roomID);

        if(!$room) {
            return [];
        }

        $user = $room->users()->where('id', $request->user()->id)->exists();
        if($user) {
            if($request->has('count')) {
                $count = $request->get('count');
            } else {
                $count = 1;
            }

            $messages = $room->messages()->orderByDesc('created_at')->skip(10*($count-1))->limit(10)->get()->sortBy('created_at');

            $messages = ChatMessageResource::collection($messages);
            $roomUsers = $room->users()->where('id', '!=', $request->user()->id)->where('last_activity_at', '>', Carbon::now()->subDays(2))->get();

            dispatch(new UpdateAllMessageSeen($messages, $roomUsers, $request->user()->id, $room->users->count() === 2));
            return $messages;
        }

        return null;
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid',
            'data' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->get('data');
        $messageID = $request->get('id');
        $room = ChatRoom::find($data['roomId']);

        /*if(isset($data['uploaded_file']) && $data['uploaded_file'] !== null) {
            $basePath = storage_path('uploads/');

            if(!File::isDirectory($basePath)) {
                File::makeDirectory($basePath, 0777, true, true);
            }

            Storage::disk('uploads')->put($data['file']['name'] . '.' . $data['file']['extension'], $data['uploaded_file']);
        }*/

        if(!$room) {
            $room = ChatRoom::create([
                'name' => 'Csoport'
            ]);

            ChatRoomUser::create([
                'room_id' => $room->id,
                'user_id' => $request->user()->id,
            ]);

            $otherUser = User::where('username', $data['room']['roomName'])->firstOrFail();

            ChatRoomUser::create([
                'room_id' => $room->id,
                'user_id' => $otherUser->id,
            ]);

            $message = ChatRoomMessage::create([
                'id' => $messageID,
                'room_id' => $room->id,
                'user_id' => $request->user()->id,
                'message' => $data['content'],
                'message_id' => $data['replyMessage'] === null ? null : $data['replyMessage']['_id']
            ]);
        } else {
            $user = $room->users()->where('id', $request->user()->id)->exists();
            if($user) {
                $message = ChatRoomMessage::create([
                    'id' => $messageID,
                    'room_id' => $data['roomId'],
                    'user_id' => $request->user()->id,
                    'message' => $data['content'],
                    'message_id' => $data['replyMessage'] === null ? null : $data['replyMessage']['_id']
                ]);
            }
        }

        $lastMessage = new ChatLastMessageResource($message, $request->user());
        $roomUsers = $room->users->filter(function($user) use ($request) {
            return $user->id !== $request->user()->id;
        });

        foreach($roomUsers as $roomUser) {
            broadcast(new ChatMessageSent($roomUser, new ChatMessageResource($message, $request->user()->id), $lastMessage));
        }

        return response()->json(['message' => new ChatMessageResource($message, $request->user()->id), 'last' => $lastMessage]);
    }

    public function users(Request $request)
    {
        $users = User::all();

        return UserMinimalResource::collection($users);
    }

    public function sendReaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|uuid',
            'reaction' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $message = ChatRoomMessage::findOrFail($request->get('message_id'));
        $data = [
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'emoji_name' => $request->get('reaction')['name']
        ];

        $existing = ChatRoomMessageReaction::where($data)->exists();

        if($existing) {
            ChatRoomMessageReaction::where($data)->delete();
        } else {
            ChatRoomMessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $request->user()->id,
                'emoji_name' => $request->get('reaction')['name']
            ]);
        }

        $message = ChatRoomMessage::findOrFail($request->get('message_id'));
        $room = ChatRoom::where('id', $message->room_id)->firstOrFail();
        $messageResource = new ChatMessageResource($message, $request->user()->id);

        $roomUsers = $room->users()->where('last_activity_at', '>', Carbon::now()->subDays(2))->get();

        foreach($roomUsers as $roomUser) {
            broadcast(new ChatMessageUpdated($roomUser->id, $messageResource));
        }

        return $messageResource;
    }

    public function search(Request $request)
    {
        $userRooms = [];

        $users = User::where('username', 'LIKE', '%'.$request->get('username').'%')->get();

        foreach($users as $user) {
            $rooms = ChatRoom::with('users')->whereHas('users', function(Builder $query) use ($request, $user) {
                $query->where('id', $request->user()->id);
                $query->orWhere('id', $user->id);
            })->get();

            $filteredRooms = $rooms->filter(function($room) use ($request, $user) {
                return $room->users->count() === 2 && ($room->users[0]->id === $user->id || $room->users[0]->id === $request->user()->id ) && ($room->users[1]->id === $user->id || $room->users[1]->id === $request->user()->id );
            });

            if($filteredRooms->count() > 0) {
                $userRooms[] = new ChatRoomResource($filteredRooms->first());
            } else {
                $userRooms[] = [
                    "roomId" => Str::uuid(),
                    "roomName" => $user->username,
                    "index" => 0,
                    "unreadCount" => 0,
                    "avatar" => $user->profile_picture ? route('cinema.userphoto', ['userProfilePicture' => $user->profile_picture->id]) : '/img/user.svg',
                    "lastMessage" => '',
                    "users" => ChatUserResource::collection([$user])
                ];
            }
        }

        return ["data" => $userRooms];
    }

    public function createGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'users' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $chatRoom = ChatRoom::create([
            'name' => $request->get('name', 'Private group') ?? 'Private group',
            'user_id' => $request->user()->id
        ]);

        $users = collect($request->get('users'))->push($request->user())->toArray();

        foreach($users as $user) {

            if(!isset($user['id'])) {
                $user = User::where(['username' => $user])->firstOrFail()->toArray();
            }

            ChatRoomUser::create([
                'room_id' => $chatRoom->id,
                'user_id' => $user['id']
            ]);

            broadcast(new ChatRoomCreated($user, $chatRoom))->toOthers();
        }

        return response('', 200);
    }

    public function updateRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $roomData = $request->get('room');
        $users = $request->get('users');
        $room = ChatRoom::find($roomData['roomId']);

        if(!$room) {
            return response('', 404);
        }

        $beenUpdate = false;

        if($room->user->id === $request->user()->id) {
            if($room->name !== $roomData['roomName']) {
                $room->update([
                    'name' => $roomData['roomName']
                ]);

                $beenUpdate = true;
            }

            foreach($users as $user) {
                if(!ChatRoomUser::where('room_id', $room->id)->where('user_id', $user['id'])->exists()) {
                    ChatRoomUser::create([
                        'room_id' => $room->id,
                        'user_id' => $user['id']
                    ]);

                    $beenUpdate = true;
                }
            }


            if($beenUpdate) {
                $room = ChatRoom::find($roomData['roomId']);
                foreach($room->users as $roomUser) {
                    broadcast(new ChatRoomCreated($roomUser, $room));
                }
            }
        }

        return response('', 200);
    }

    public function unread(Request $request) {
        $chatRooms = ChatRoom::whereHas('users', function(Builder $query) use ($request) {
            $query->where('id', $request->user()->id);
        })->get();

        $unread = 0;

        foreach($chatRooms as $chatRoom) {
            if($chatRoom->lastMessage()) {
                $lastMessage = (new ChatLastMessageResource($chatRoom->lastMessage()))->resolve();

                if($lastMessage['new'] === 1) {
                    $unread++;
                }
            }
        }

        return response()->json(['chat' => $unread, 'requests' => RequestNotification::where('user_id', $request->user()->id)->where('seen', 0)->count()]);
    }
}
