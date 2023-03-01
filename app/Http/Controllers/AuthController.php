<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'surname' => ['required', 'max:255'],
            'company_name' => ['required', 'max:255', 'unique:companies,name'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id'=> ['required', 'exists:cities,id'],
            'email' => ['required', 'unique:users', 'max:255', 'email'],
            'password' => ['required', 'max:20'],
            'conf_password' => ['required', 'same:password']
        ]);

        return response($this->service->register($data), 200);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ]);

        return response(['token' => $this->service->login($data)], 200);
    }
}
