<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Enums\StatusEnum;
use \App\Enums\MovieTypeEnum;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', StatusEnum::getValues())
                ->default(StatusEnum::Pending);
            $table->enum('type', MovieTypeEnum::getValues())
                ->default(MovieTypeEnum::Movie);
            $table->integer('year');
            $table->integer('season')->default(0);
            $table->integer('length');
            $table->boolean('is_premier')->default(0);

            $table->string('imdb_id')->index()->nullable();
            $table->double('imdb_rating');
            $table->bigInteger('imdb_votes');

            $table->uuid('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');

            $table->string('porthu_id')->nullable();

            $table->timestamps();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('watched_at')->nullable();
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
        Schema::dropIfExists('movies');
    }
}
