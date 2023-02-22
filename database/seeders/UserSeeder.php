<?php

namespace Database\Seeders;

use App\Models\Company;
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
            'role_id' => Role::COMPANY_ADMIN,
            'surname' => 'Admin'
        ]);

        Company::create([
            'active' => true,
            'bank_name' => 'Kaspi bank',
            'bik' => '12345678',
            'bin' => '12345678',
            'full_name' => 'Admin Adminov Admin',
            'iik' => '12345678',
            'name' => 'TOO ADMIN',
            'phone_number' => '123456789',
            'user_id' => $admin->id,
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
