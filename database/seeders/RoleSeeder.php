<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'ADMIN'
        ]);
        Role::create([
            'name' => 'Company Admin'
        ]);
        Role::create([
            'name' => 'USER'
        ]);
    }
}
