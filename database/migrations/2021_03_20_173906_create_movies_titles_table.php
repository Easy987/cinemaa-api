<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies_titles', function (Blueprint $table) {
            $table->uuid('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('CASCADE');
            $table->string('lang');
            $table->string('title');
            $table->string('slug');

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
        Schema::dropIfExists('movies_titles');
    }
}
