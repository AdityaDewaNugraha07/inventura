<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@inventura.test'],
            [
                'name'     => 'Admin Utama',
                'password' => Hash::make('password'),
            ]
        );
    }
}
