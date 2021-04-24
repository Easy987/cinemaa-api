<?php

namespace Database\Factories\Movie;

use App\Models\Movie\Movie;
use App\Models\Movie\MovieWriter;
use App\Models\Movie\Writer;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieWriterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MovieWriter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'movie_id' => Movie::inRandomOrder()->limit(1)->first()->id,
            'writer_id' => Writer::inRandomOrder()->limit(1)->first()->id,
        ];
    }
}
