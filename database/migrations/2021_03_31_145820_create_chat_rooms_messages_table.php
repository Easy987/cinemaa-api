<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRoomsMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('room_id');
            $table->foreign('room_id', 'chat_users_messages_room_id')->references('id')->on('chat_rooms')->onDelete('CASCADE');

            $table->uuid('user_id');
            $table->foreign('user_id', 'chat_users_messages_user_id')->references('id')->on('users')->onDelete('CASCADE');

            $table->uuid('message_id')->nullable();

            $table->text('message');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('chat_rooms_messages', function(Blueprint $table) {
            $table->foreign('message_id', 'chat_users_messages_message_id')->references('id')->on('chat_rooms_messages')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_rooms_messages');
    }
}
