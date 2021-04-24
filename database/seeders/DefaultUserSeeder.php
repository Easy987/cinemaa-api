<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Faker\Provider\de_CH\Text;
use Faker\Provider\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mainUser = User::firstOrCreate(
            ['username' => 'SYSTEM'],
            [
            'username' => 'SYSTEM',
            'email' => 'cinemaa@protonmail.com',
            'email_verified_at' => now(),
            'password' => 'kwTyxcQAKnRVrf9ChTFquwH5VwDw6BbJ', // password
        ]);

        UserRole::firstOrCreate(['role_id' => Role::findByName('owner')->id, 'model_type' => User::class, 'model_id' => $mainUser->id]);
    }
}
