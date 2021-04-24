<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            GenreSeeder::class,
            LinkTypeSeeder::class,
            LanguageTypeSeeder::class,
            DefaultUserSeeder::class,
            ForumSeeder::class,
            DefaultChatRoomSeeder::class
        ]);
    }
}
