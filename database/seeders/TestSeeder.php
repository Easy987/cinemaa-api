<?php

namespace Database\Seeders;

use App\Models\Movie\Actor;
use App\Models\Movie\Director;
use App\Models\Movie\Movie;
use App\Models\Movie\Writer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    protected $count = 50;

    public function run()
    {
        /*
        Actor::factory()->count($this->count)->create();
        Director::factory()->count($this->count)->create();
        Writer::factory()->count($this->count)->create();

        Movie::factory()->count($this->count)->create();
        */
        User::factory()->count(100)->create();
    }
}
