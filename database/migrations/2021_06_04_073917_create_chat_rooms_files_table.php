<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRoomsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms_files', function (Blueprint $table) {
            $table->uuid('id');

            $table->uuid('room_id');
            $table->foreign('room_id', 'chat_files_room_id')->references('id')->on('chat_rooms')->onDelete('CASCADE');
            $table->uuid('user_id');
            $table->foreign('user_id', 'chat_files_user_id')->references('id')->on('users')->onDelete('CASCADE');

            $table->string('extension');
            $table->text('name');
            $table->unsignedBigInteger('size');
            $table->string('type');

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
        Schema::dropIfExists('chat_rooms_files');
    }
}
