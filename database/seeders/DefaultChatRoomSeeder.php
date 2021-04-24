<?php

namespace Database\Seeders;

use App\Models\ChatRoom\ChatRoom;
use App\Models\ChatRoom\ChatRoomUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultChatRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chatRoom = ChatRoom::firstOrCreate([
            'id' => '93343d2f-6d8b-463e-b4d3-347ef04a461C'
        ],
        [
            'name' => 'Cinemaa.cc',
            'user_id' => User::where('username', 'SYSTEM')->first()->id
        ]);

        $users = User::all();

        foreach($users as $user) {
            ChatRoomUser::firstOrCreate([
                'room_id' => $chatRoom->id,
                'user_id' => $user->id
            ]);
        }
    }
}
