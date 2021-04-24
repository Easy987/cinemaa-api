<?php

namespace Database\Seeders;

use App\Models\Movie\LinkType;
use Illuminate\Database\Seeder;

class LinkTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $linkTypes = config('cinema.link_types');

        foreach($linkTypes as $linkType) {
            LinkType::create([
                'name' => $linkType
            ]);
        }
    }
}
