<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdToMoviesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movies_genres', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_actors', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_descriptions', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_directors', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_favourites', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_ratings', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_titles', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_to_be_watched', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_watched', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('movies_writers', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies_genres', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_actors', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_descriptions', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_directors', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_favourites', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_ratings', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_titles', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_to_be_watched', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_watched', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('movies_writers', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
}
