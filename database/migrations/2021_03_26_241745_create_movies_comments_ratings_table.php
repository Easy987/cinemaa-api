<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesCommentsRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies_comments_ratings', function (Blueprint $table) {
            $table->uuid('comment_id');
            $table->foreign('comment_id')->references('id')->on('movies_comments')->onDelete('CASCADE');

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');

            $table->boolean('type')->comment('0 = dislike, 1 = like');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies_comments_ratings');
    }
}
