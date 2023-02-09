<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Project
        Permission::create([
            'name' => 'project-index',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'project-create',
            'label' => 'create',
        ]);
        Permission::create([
            'name' => 'project-edit',
            'label' => 'edit',
        ]);
        Permission::create([
            'name' => 'project-delete',
            'label' => 'delete',
        ]);

        //Test
        Permission::create([
            'name' => 'test-index',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'test-create',
            'label' => 'create',
        ]);
        Permission::create([
            'name' => 'test-edit',
            'label' => 'edit',
        ]);
        Permission::create([
            'name' => 'test-delete',
            'label' => 'delete',
        ]);

        $permissionsAdmin = Permission::all();
        $permissionsUser = Permission::where('name', '=', "%test%")->get();


        foreach($permissionsAdmin as $permission){
            $permission->roles()->attach(Role::ADMIN);
        }

        foreach($permissionsUser as $permission){
            $permission->roles()->attach(Role::USER);
        }
    }
}
