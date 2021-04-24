<?php

use App\Enums\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies_links', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('CASCADE');

            $table->uuid('site_id')->nullable();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('CASCADE');

            $table->uuid('link_type_id');
            $table->foreign('link_type_id')->references('id')->on('link_types')->onDelete('CASCADE');

            $table->uuid('language_type_id');
            $table->foreign('language_type_id')->references('id')->on('language_types')->onDelete('CASCADE');

            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');

            $table->enum('status', StatusEnum::getValues())
                ->default(StatusEnum::Pending);

            $table->string('link');
            $table->bigInteger('part')->default(0);
            $table->bigInteger('season')->default(0);
            $table->bigInteger('episode')->default(0);

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
        Schema::dropIfExists('movies_links');
    }
}
