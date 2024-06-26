<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRoomsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms_users', function (Blueprint $table) {
            $table->uuid('room_id');
            $table->foreign('room_id', 'chat_users_room_id')->references('id')->on('chat_rooms')->onDelete('CASCADE');
            $table->uuid('user_id');
            $table->foreign('user_id', 'chat_users_user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_rooms_users');
    }
}
