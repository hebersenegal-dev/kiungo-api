<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Kiungo',
            'email' => 'admin@kiungo.test',
            'password' => Hash::make('password'),
            'role' => 'professional',
            'country_id' => 1,
            'is_verified' => true,
        ]);

        // Professional
        User::create([
            'first_name' => 'Fatou',
            'last_name' => 'Diop',
            'email' => 'fatou@pro.test',
            'password' => Hash::make('password'),
            'role' => 'professional',
            'bio' => 'Ingénieure en génie civil.',
            'country_id' => 1,
            'is_verified' => true,
            'verified_by' => $admin->id,
        ]);

        // Structure
        User::create([
            'company_name' => 'BTP Solutions',
            'email' => 'contact@btp-solutions.sn',
            'password' => Hash::make('password'),
            'role' => 'structure',
            'sector' => 'Construction',
            'country_id' => 1,
            'website' => 'https://btp-solutions.sn',
            'is_verified' => true,
            'verified_by' => $admin->id,
        ]);
    }
}
