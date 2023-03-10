<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show()
    {
        $company = Company::whereHas('users', function ($user){
            $user->where('id', auth('sanctum')->id());
        })->first();

        return response($company, 200);
    }
}
