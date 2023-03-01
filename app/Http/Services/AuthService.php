<?php

namespace App\Http\Services;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;

class AuthService
{
    public function register($data): String
    {
        $user = User::create($data);
        $user->roles()->attach(Role::ADMIN);

        Company::create([
            'user_id' => $user->id,
            'name' => $data['company_name']
        ]);

        return $user->email;
    }

    public function login($data): String
    {
        $user = \Auth::attempt($data);

        if(!$user){
            return response(['message' => 'Unauthorized'], 401);
        }

        return request()->user()->createToken(\Str::random(10))->plainTextToken;
    }
}
