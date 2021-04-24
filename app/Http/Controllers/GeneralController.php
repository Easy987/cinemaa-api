<?php

namespace App\Http\Controllers;

use App\Events\MessageBoardMessageSent;
use App\Events\RequestReceived;
use App\Http\Resources\MessageBoardResource;
use App\Http\Resources\RequestResource;
use App\Models\MessageBoardChat;
use App\Models\RequestNotification;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function requests(Request $request)
    {
        $requests = \App\Models\Request::query()->orderBy('created_at', 'desc');

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
}
