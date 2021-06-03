<?php

namespace App\Http\Controllers;

use App\Http\Resources\ForumDiscussionResource;
use App\Http\Resources\ForumPostResource;
use App\Http\Resources\ForumTopicResource;
use App\Http\Resources\Movie\MovieResource;
use App\Models\Forum\ForumDiscussion;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumPostReaction;
use App\Models\Forum\ForumPostSeen;
use App\Models\Forum\ForumTopic;
use App\Models\Movie\MovieComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumController extends Controller
{
    public function discussions(Request $request)
    {
        return ForumDiscussionResource::collection(ForumDiscussion::withCount('topics', 'posts')->get());
    }

    public function topics(Request $request, $id)
    {
        $discussion = ForumDiscussion::findOrFail($id);

        views($discussion)->cooldown(10)->record();

        return ForumTopicResource::collection(ForumTopic::where('discussion_id', $id)->withCount('posts')->get());
    }

    public function topic(Request $request, $id, $topicID)
    {
        $discussion = ForumDiscussion::findOrFail($id);
        $topic = ForumTopic::findOrFail($topicID);

        views($topic)->cooldown(10)->record();

        return new ForumTopicResource($topic);
    }

    public function posts(Request $request, $id, $topicID)
    {
        $discussion = ForumDiscussion::findOrFail($id);
        $topic = ForumTopic::findOrFail($topicID);

        views($topic)->cooldown(10)->record();

        if($topic->posts->count() > 0) {
            ForumPostSeen::firstOrCreate([
                'post_id' => $topic->posts()->latest()->first()->id,
                'user_id' => $request->user()->id
            ]);
        }

        return ForumPostResource::collection($topic->posts()->orderBy('created_at')->paginate());
    }

    public function postLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|string',
            'type' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $postID = $request->get('post_id');
        $userID = auth()->user()->id;
        $type = $request->get('type');
        $post = ForumPost::findOrFail($postID);

        $currentRating = ForumPostReaction::where('user_id', $userID)->where('post_id', $postID)->first();

        if(!$currentRating) {
            ForumPostReaction::create([
                'post_id' => $postID,
                'user_id' => $userID,
                'type' => $type
            ]);
        } elseif($currentRating && $currentRating->type !== $type) {
            ForumPostReaction::where('user_id', $userID)->where('post_id', $postID)->update(['type' => $type]);
        } elseif($currentRating && $currentRating->type === $type) {
            ForumPostReaction::where('user_id', $userID)->where('post_id', $postID)->delete();
        }

        return ForumPostResource::collection($post->topic->posts()->orderBy('created_at')->paginate());
    }

    public function postDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $post = ForumPost::findOrFail($request->get('post_id'));

        if($post->user->id === $request->user()->id || $request->user()->can('comments.delete'))
        {
            $post->delete();
        }

        return ForumPostResource::collection($post->topic->posts()->orderBy('created_at')->paginate());
    }

    public function post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic_id' => 'required|string',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $topic = ForumTopic::findOrFail($request->get('topic_id'));

        if($topic->posts()->count() === 0 && !$request->user()->can('admin.forums.index')) {
            return response(null, 403);
        }

        ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => $request->user()->id,
            'message' => $request->get('message')
        ]);

        return ForumPostResource::collection($topic->posts()->orderBy('created_at')->paginate());
    }
}
