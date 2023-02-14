<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::controller(VerifyEmailController::class)->group(function (){
    Route::post('verify/email', 'verifyEmail');
    Route::post('resend/email', 'resendEmail');
});

Route::middleware('auth:sanctum')->group(function(){
    Route::group(['prefix' => 'projects'], function(){
        Route::controller(ProjectController::class)->group(function(){
            Route::get('index', 'index');
            Route::post( 'post', 'store');
            Route::match(['get', 'post'],'edit/{project}', 'edit');
            Route::delete('delete/{project}', 'destroy');
        });
    });
});
