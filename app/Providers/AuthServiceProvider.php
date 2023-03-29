<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $permission, $project = null) {

            if($user->rolePermissions()->contains($permission) || $user->userAbilities()->contains($permission)){
                return true;
            }

            if($project){
                if(request()->isMethod('put') || request()->isMethod('get')){
                    if($permission = Permission::where('name', $permission)->first()){
                        return $user->userProjectPermission($project[0]->id, $permission->id);
                    }
                }
            }

            return false;
        });
    }
}
