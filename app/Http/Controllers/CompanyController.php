<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable'],
            'bank_name' => ['required', 'string', 'max:50'],
            'name' => ['required', 'unique:companies', 'max:255'],
            'bin' => ['required', 'unique:companies', 'max:255'],
            'bik' => ['required', 'unique:companies', 'max:255'],
            'iik' => ['required', 'unique:companies', 'max:255'],
            'phone_number' => ['required', 'unique:companies', 'string', 'starts_with:+7', 'max:12', 'min:12'],
            'full_name' => ['max:255', 'string'],
        ]);

        $data['user_id'] = auth('sacntum')->id();

        Company::create($data);

        return response(['message' => 'Company created successfully'], 200);
    }
}
