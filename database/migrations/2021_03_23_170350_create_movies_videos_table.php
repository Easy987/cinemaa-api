<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies_videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('movie_id');
            $table->uuid('user_id')->nullable();
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('CASCADE');
            $table->string('youtube_id');
            $table->enum('status', \App\Enums\StatusEnum::getValues())
                ->default(\App\Enums\StatusEnum::Pending);

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
        Schema::dropIfExists('movies_videos');
    }
}
