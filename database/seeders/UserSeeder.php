<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('username', 'superadmin')->exists()) {
            User::create([
                'username' => 'superadmin',
                'full_name' => 'Super Admin',
                'email' => 'Super@Admin.com',
                'phone_number' => '1244567890',
                'address' => 'HQ',
                'password' => 'password',
            ])->assignRole('super admin');
        }

        if (!User::where('username', 'superadminrm')->exists()) {
        User::create([
            'username' => 'superadminrm',
            'full_name' => 'Super Admin',
            'email' => 'Superrn@Admin.com',
            'phone_number' => '123455690',
            'address' => 'HQ',
            'password' => 'password',
        ])->assignRole('super admin');

        }
        if (!User::where('username', 'superadminma')->exists()) {
        User::create([
            'username' => 'superadminma',
            'full_name' => 'Super Admin',
            'email' => 'Superm@Admin.com',
            'phone_number' => '1234557690',
            'address' => 'HQ',
            'password' => 'password',
        ])->assignRole('super admin');
        }
    }
}
