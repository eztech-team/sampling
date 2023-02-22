<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    const ADMIN = '1';
    const COMPANY_ADMIN = '2';
    const USER = '3';

    protected $guarded = [];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
