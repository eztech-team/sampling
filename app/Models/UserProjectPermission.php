<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProjectPermission extends Model
{
    use HasFactory;

    protected $table = 'permission_project_user';
    protected $guarded = [];
}
