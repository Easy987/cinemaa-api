<?php

namespace Database\Seeders;

use App\Models\Movie\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = config('cinema.genre_list');

        foreach($genres as $genre) {
            Genre::firstOrCreate(['name' => Str::lower($genre)]);
        }
    }
}
