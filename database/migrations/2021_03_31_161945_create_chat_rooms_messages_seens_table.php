<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRoomsMessagesSeensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms_messages_seens', function (Blueprint $table) {
            $table->uuid('message_id');
            $table->foreign('message_id', 'chat_users_messages_seens_message_id')->references('id')->on('chat_rooms_messages')->onDelete('CASCADE');
            $table->uuid('user_id');
            $table->foreign('user_id', 'chat_users_messages_seens_user_id')->references('id')->on('users')->onDelete('CASCADE');
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
        Schema::dropIfExists('chat_rooms_messages_seens');
    }
}
