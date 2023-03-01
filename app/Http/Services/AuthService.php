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

    public function createUser($data)
    {
        $user = User::create([
            'email' => $data->email,
            'role_id' => $data->role_id,
            'password' => $data->password,
            'name' => $data->name,
            'surname' => $data->surname,
            'country_id' => $data->country_id,
            'city_id' => $data->city_id
        ]);

        $company = Company::create([
            'user_id' => $user->id,
            'name' => $data->company_name
        ]);

        $company->user()->attach($user->id);

        return $user;
    }

    public function login($data): String
    {
        $user = \Auth::attempt($data);

        if(!$user){
            return response(['message' => 'Unauthorized'], 401);
        }

        return request()->user()->createToken(\Str::random(10))->plainTextToken;
    }

    public function sendCodeToEmail($request)
    {
        $this->sendCodeToUserEmail($request);

        return $request->email;
    }
}
