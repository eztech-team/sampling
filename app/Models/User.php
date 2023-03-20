<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'city_id',
        'country_id',
        'email',
        'password',
        'role_id',
        'code',
        'email_verified_at',
        'email_verification_send'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_send' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function rolePermissions()
    {
        return $this->role->permissions
            ->flatten()->pluck('name')->unique();
    }

    public function abilities()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function userAbilities()
    {
        return $this->abilities->pluck('name')->unique();
    }

    public function userProjects()
    {
        return $this->belongsToMany(Project::class, 'permission_project_user', 'id', 'user_id');
    }


    public function userProjectPermission($project_id, $permission_id)
    {
        return (bool)UserProjectPermission::where('permission_id', $permission_id)->where('project_id', $project_id)->first();
    }

    public function company()
    {
        return $this->belongsToMany(Company::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

}
