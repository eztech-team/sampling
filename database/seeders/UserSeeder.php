<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'password' => Hash::make('password'),
            'email' => 'admin@admin.com',
            'name' => 'Admin',
            'city_id' => 1,
            'iin' => '000000000000',
            'phone_number' => '77474991203',
            'role_id' => Role::ADMIN,
            'surname' => 'Admin'
        ]);

        $user = User::create([
            'password' => Hash::make('password'),
            'email' => 'user@user.com',
            'name' => 'User',
            'city_id' => 1,
            'iin' => '000000000001',
            'phone_number' => '77474991201',
            'role_id' => Role::USER,
            'surname' => 'User'
        ]);
    }
}
