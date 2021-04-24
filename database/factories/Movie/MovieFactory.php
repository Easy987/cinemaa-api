<?php

namespace Database\Factories\Movie;

use App\Enums\MovieTypeEnum;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieActor;
use App\Models\Movie\MovieDescription;
use App\Models\Movie\MovieDirector;
use App\Models\Movie\MovieGenre;
use App\Models\Movie\MovieName;
use App\Models\Movie\MovieTitle;
use App\Models\Movie\MovieWriter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovieFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Movie::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'year' => $this->faker->year,
            'length' => $this->faker->numberBetween(60, 150),
            'imdb_id' => Str::random(8),
            'imdb_rating' => $this->faker->numberBetween(0, 100)/10,
            'imdb_votes' => $this->faker->numberBetween(5000, 500000),
            'user_id' => User::first()->id,
            'porthu_id' => Str::random(8),
            'accepted_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Movie $movie) {
            MovieActor::factory()->count($this->faker->numberBetween(1,3))->create([
                'movie_id' => $movie->id,
            ]);

            MovieDirector::factory()->count($this->faker->numberBetween(1,2))->create([
                'movie_id' => $movie->id,
            ]);

            MovieGenre::factory()->count($this->faker->numberBetween(1,3))->create([
                'movie_id' => $movie->id,
            ]);

            MovieWriter::factory()->count($this->faker->numberBetween(1,2))->create([
                'movie_id' => $movie->id,
            ]);

            $langs = config('cinema.allowed_langs');

            foreach($langs as $lang) {
                MovieTitle::factory()->create([
                    'movie_id' => $movie->id,
                    'lang' => $lang,
                ]);

                MovieDescription::factory()->create([
                    'movie_id' => $movie->id,
                    'lang' => $lang,
                ]);
            }
        });
    }
}
