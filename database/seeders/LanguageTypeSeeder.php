<?php

namespace Database\Seeders;

use App\Models\Movie\LanguageType;
use Illuminate\Database\Seeder;

class LanguageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languageTypes = config('cinema.language_types');

        foreach($languageTypes as $languageType) {
            LanguageType::create([
                'name' => $languageType
            ]);
        }
    }
}
