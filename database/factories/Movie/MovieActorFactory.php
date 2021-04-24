<?php

namespace Database\Factories\Movie;

use App\Models\Movie\Actor;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieActor;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieActorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MovieActor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'movie_id' => Movie::inRandomOrder()->limit(1)->first()->id,
            'actor_id' => Actor::inRandomOrder()->limit(1)->first()->id,
        ];
    }
}
