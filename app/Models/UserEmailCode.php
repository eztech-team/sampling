<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmailCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'city_id',
        'country_id',
        'email',
        'password',
        'role_id',
        'code',
        'company_name',
        'email_verification_send'
    ];

    protected $casts = [
        'email_verification_send' => 'datetime'
    ];
}
