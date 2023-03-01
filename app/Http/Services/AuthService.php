<?php

namespace App\Http\Services;

use App\Http\Traits\Message;
use App\Mail\SendCodeMail;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    use Message;

    public function register($data)
    {
        $data['role_id'] = Role::ADMIN;
        $user = User::create($data);

        Company::create([
            'user_id' => $user->id,
            'name' => $data['company_name']
        ]);

        $this->sendCodeToUserEmail($user);

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
