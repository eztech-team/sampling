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
            'middle_name' => ['nullable', 'max:255'],
            'city_id'=> ['required', 'exists:cities'],
            'iin' => ['required', 'min:12', 'max:12', 'numeric', 'unique:users'],
            'email' => ['required', 'unique:users', 'max:255', 'email'],
            'phone_number' => ['required', 'unique:users']
        ]);

        return response(['email' => $this->service->register($data)], 200);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'iin' => ['required'],
            'password' => ['required'],
        ]);

        return response(['token' => $this->service->login($data)], 200);
    }
}
