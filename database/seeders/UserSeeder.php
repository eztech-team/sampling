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
            'country_id' => 1,
            'city_id' => 1,
            'role_id' => Role::COMPANY_ADMIN,
            'surname' => 'Admin',
            'email_verification_send' => now()
        ]);

        $company = Company::create([
            'active' => true,
            'name' => 'TOO ADMIN',
            'user_id' => $admin->id,
        ]);

        $sampling = User::create([
            'password' => Hash::make('password'),
            'email' => 'sampling@admin.com',
            'name' => 'Sampling',
            'country_id' => 1,
            'city_id' => 1,
            'role_id' => Role::COMPANY_ADMIN,
            'surname' => 'Sampling',
            'email_verification_send' => now()
        ]);

        $companySampling = Company::create([
            'active' => true,
            'name' => 'TOO Sampling',
            'user_id' => $admin->id,
        ]);

        $companySampling->users()->attach($sampling->id);

        $user = User::create([
            'password' => Hash::make('password'),
            'email' => 'user@user.com',
            'name' => 'User',
            'country_id' => 1,
            'city_id' => 1,
            'role_id' => Role::USER,
            'surname' => 'User'
        ]);
    }
}
