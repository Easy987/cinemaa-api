<?php

namespace Database\Factories\Movie;

use App\Models\Movie\Director;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieDirector;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieDirectorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MovieDirector::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'movie_id' => Movie::inRandomOrder()->limit(1)->first()->id,
            'director_id' => Director::inRandomOrder()->limit(1)->first()->id,
        ];
    }
}
