<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserStatusEnum;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('secret_uuid');
            $table->string('username')->unique();
            $table->string('email');
            $table->string('password');
            $table->dateTime('email_verified_at')->nullable();
            $table->enum('status', UserStatusEnum::getValues())
                ->default(UserStatusEnum::Unverified);
            $table->text('about')->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('gender')->default(0);
            $table->boolean('public_name')->default(1);
            $table->timestamps();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
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
        Schema::dropIfExists('users');
    }
}
