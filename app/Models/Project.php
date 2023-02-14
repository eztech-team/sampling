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
        'audit_conf'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
