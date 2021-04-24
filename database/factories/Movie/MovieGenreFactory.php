<?php

namespace Database\Factories\Movie;

use App\Models\Movie\Genre;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieGenre;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieGenreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MovieGenre::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'movie_id' => Movie::inRandomOrder()->limit(1)->first()->id,
            'genre_id' => Genre::inRandomOrder()->limit(1)->first()->id,
        ];
    }
}
