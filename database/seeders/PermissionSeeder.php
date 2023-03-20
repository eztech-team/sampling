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
        // Balance and Income test
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
        // Balance and Income item
        Permission::create([
            'name' => 'item-index',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'item-create',
            'label' => 'create',
        ]);
        Permission::create([
            'name' => 'item-edit',
            'label' => 'edit',
        ]);
        Permission::create([
            'name' => 'item-delete',
            'label' => 'delete',
        ]);
        //Team
        Permission::create([
            'name' => 'team-index',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'team-create',
            'label' => 'create',
        ]);
        Permission::create([
            'name' => 'team-edit',
            'label' => 'edit',
        ]);
        Permission::create([
            'name' => 'team-delete',
            'label' => 'delete',
        ]);
        //User create company
        Permission::create([
            'name' => 'user-index',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'user-store',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'user-edit',
            'label' => 'edit',
        ]);
        Permission::create([
            'name' => 'user-delete',
            'label' => 'delete',
        ]);
        //Aggregate
        Permission::create([
            'name' => 'aggregate-index',
            'label' => 'index',
        ]);
        Permission::create([
            'name' => 'aggregate-create',
            'label' => 'create',
        ]);
        Permission::create([
            'name' => 'aggregate-edit',
            'label' => 'edit',
        ]);
        Permission::create([
            'name' => 'aggregate-delete',
            'label' => 'delete',
        ]);
        //Company
        Permission::create([
            'name' => 'company-show',
            'label' => 'store',
        ]);
        /*
         * After creating page for moderators to site we need to attach some permissions for moderator.
         * Now I have no idea what I will give them.
         * */
//        $permissionSiteModerator = Permission::where('name', '=', "company-accept")->get();

        $permissionsAdmin = Permission::all(); // for company Admins


        $permissionsUserTest = Permission::where('name', 'like', "%test%")
            ->get();
        $permissionsUserItem = Permission::where('name', 'like', "%item%")
            ->get();


        foreach($permissionsAdmin as $permission){
            $permission->roles()->attach(Role::COMPANY_ADMIN);
        }

        foreach($permissionsUserTest as $permission){
            $permission->roles()->attach(Role::USER);
        }
        foreach($permissionsUserItem as $permission){
            $permission->roles()->attach(Role::USER);
        }
    }
}
