<?php

namespace Database\Seeders;

use App\Models\Forum\ForumDiscussion;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumTopic;
use App\Models\User;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $discussion = ForumDiscussion::create([
            'name' => 'Szabályzat',
            'icon' => 'align-justify',
            'description' => 'Itt a szabályzatot olvashatjátok'
        ]);

        $topic = ForumTopic::create([
            'discussion_id' => $discussion->id,
            'name' => 'Jogi szabyálzat',
            'description' => 'Itt a jogi szabályzatot láthatjátok',
        ]);

        $post = ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => User::first()->id,
            'message' => 'TESZTTTT'
        ]);
    }
}
