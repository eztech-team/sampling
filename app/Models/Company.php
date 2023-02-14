<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'name',
        'bin',
        'bik',
        'iik',
        'phone_number',
        'full_name',
    ];
}
