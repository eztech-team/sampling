<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'start_period',
        'end_period',
        'supervisor_conf',
        'audit_conf',
        'active',
        'general_level',
        'operating_level'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function projectPermission()
    {
        return $this->hasMany(UserProjectPermission::class, 'project_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

}
