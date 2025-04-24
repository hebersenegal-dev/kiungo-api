<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Alioune',
            'last_name' => 'Ba',
            'username' => 'aliouneba',
            'email' => 'alioune@example.com',
            'password' => Hash::make('password123'),
            'location_id' => 1,
            'role' => 'professional'
        ]);
    }
}
