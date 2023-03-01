<?php

use App\Http\Controllers\AggregateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceItemController;
use App\Http\Controllers\BalanceTestController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\IncomeItemController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TestController;
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
    Route::group(['prefix' => 'companies'], function (){
        Route::controller(CompanyController::class)->group(function (){
            Route::post('/', 'store');
        });
    });
    /* *
     * *********** Projects **********
     * */
    Route::group(['prefix' => 'projects'], function(){
        Route::controller(ProjectController::class)->group(function(){
            Route::get('/', 'index');
            Route::post( '/', 'store');
            Route::get('/{project}', 'show');

            Route::put( '/{project}', 'update');
            Route::put( '/edit/level/{project}', 'updateLevel');
            Route::delete('{project}', 'destroy');
            Route::put( '/edit/level/{project}', 'updateLevel');
        });
    });
    /* *
     * *********** Балансовые статьи **********
     * */
    Route::group(['prefix' => 'balance-item'], function(){
        Route::controller(BalanceItemController::class)->group(function(){
            Route::get('/', 'index');
            Route::post( '/', 'store');
            Route::get('/{balanceItem}', 'show');

            Route::put( '/{balanceItem}', 'update')->middleware('check.project');
            Route::delete('{balanceItem}', 'destroy')->middleware('check.project');;
        });
    });
    /* *
     * *********** Выберите или загрузите совокупность **********
     * */
    Route::group(['prefix' => 'aggregate'], function(){
        Route::controller(AggregateController::class)->group(function(){
            Route::get('/', 'index');
            Route::post( '/', 'store');
        });
    });
    /* *
     * *********** Статья отчета о прибылях и убытках **********
     * */
    Route::group(['prefix' => 'income-item'], function(){
        Route::controller(IncomeItemController::class)->group(function(){
            Route::get('/', 'index');
            Route::post( '/', 'store');
            Route::get('/{incomeItem}', 'show');

            Route::put( '/{incomeItem}', 'update')->middleware('check.project');
            Route::delete('/{incomeItem}', 'destroy')->middleware('check.project');;
        });
    });

    /*
     * TOC”s
     * */

    Route::group(['prefix' => 'toc-s'], function (){
        Route::controller(BalanceTestController::class)->group(function (){
            Route::get('/{project_id}', 'index');
            Route::post('/', 'store');
            Route::get('/{balanceTest}', 'show');
        });
    });
});
