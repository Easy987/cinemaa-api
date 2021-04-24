<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBadLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bad_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('reportable');
            $table->boolean('type')->default(0);
            $table->uuid('user_id');
            $table->uuid('movie_id');
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
        Schema::dropIfExists('bad_links');
    }
}
