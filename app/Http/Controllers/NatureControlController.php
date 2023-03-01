<?php

namespace App\Http\Controllers;

use App\Models\NatureControl;
use Illuminate\Http\Request;

class NatureControlController extends Controller
{
    public function index()
    {
        return response(NatureControl::get());
    }
}
