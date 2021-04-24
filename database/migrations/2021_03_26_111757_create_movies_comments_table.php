<?php

use App\Enums\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('CASCADE');

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');

            $table->enum('status', StatusEnum::getValues())
                ->default(StatusEnum::Pending);

            $table->text('comment');

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
        Schema::dropIfExists('movies_comments');
    }
}
