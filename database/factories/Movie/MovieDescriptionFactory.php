<?php

namespace Database\Factories\Movie;

use App\Models\Movie\Movie;
use App\Models\Movie\MovieDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieDescriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MovieDescription::class;

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
            'description' => $this->faker->text(500),
        ];
    }
}
