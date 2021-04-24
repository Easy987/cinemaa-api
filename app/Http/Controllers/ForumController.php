<?php

namespace App\Http\Controllers;

use App\Http\Resources\ForumDiscussionResource;
use App\Http\Resources\ForumPostResource;
use App\Http\Resources\ForumTopicResource;
use App\Models\Forum\ForumDiscussion;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumTopic;
use Illuminate\Http\Request;

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

        return ForumPostResource::collection($topic->posts);
    }
}
