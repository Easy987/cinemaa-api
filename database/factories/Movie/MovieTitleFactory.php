<?php

namespace Database\Factories\Movie;

use App\Models\Movie\Movie;
use App\Models\Movie\MovieTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieTitleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MovieTitle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'movie_id' => Movie::inRandomOrder()->limit(1)->first()->id,
            'lang' => $this->faker->randomElement(config('cinema.allowed_langs')),
            'title' => $this->faker->sentence($this->faker->numberBetween(1,3)),
        ];
    }
}
