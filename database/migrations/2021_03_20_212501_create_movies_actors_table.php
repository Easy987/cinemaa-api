<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesActorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies_actors', function (Blueprint $table) {
            $table->uuid('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('CASCADE');
            $table->uuid('actor_id');
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('CASCADE');

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
        Schema::dropIfExists('movies_actors');
    }
}
