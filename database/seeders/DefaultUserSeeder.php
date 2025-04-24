<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            [
              'first_name' => 'SuperAdmin',
           'last_name' => 'Kiungo',
           'username' => 'superadmin',
           'email' => 'superadmin@kiungo.com',
           'password' => Hash::make('Kiungoaccess#25'),
           'location_id' => 1,
           'role' => 'admin',
            ]
        );
    }
}
