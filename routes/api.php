<?php

use App\Http\Controllers\AggregateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceItemController;
use App\Http\Controllers\BalanceTestController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\IncomeItemController;
use App\Http\Controllers\IncomeTestController;
use App\Http\Controllers\NatureControlController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResultTocController;
use App\Http\Controllers\TdController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Models\Company;
use App\Models\Permission;
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

Route::post('company/accept', function (){
    $companyID = request()->company_id;
    $company = Company::find($companyID);
    $company->active = true;
    $company->save();

    return response('OK', 200);
});

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('send-code', 'sendCode');
});

Route::controller(VerifyEmailController::class)->group(function (){
    Route::post('verify/email', 'verifyEmail');
    Route::post('resend/email', 'resendEmail');
});

Route::prefix('companies')->group(function (){
    Route::post('create-user', [UserController::class, 'createUser']);

    Route::controller(UserController::class)
        ->middleware('auth:sanctum')
        ->group(function (){
            Route::post('send-notification', 'sendNotification');
            Route::get('users', 'users');
            Route::get('users/role', 'usersByRole');
            Route::post('add-project-team', 'addUserToProjectsAndTeam');
    });
});

Route::middleware(['auth:sanctum', 'check.company'])->group(function(){
    Route::group(['prefix' => 'companies'], function (){
        Route::controller(CompanyController::class)->group(function (){
            Route::post('/', 'show');
        });
    });
    /*
     * ********** Get teams and users ********
     * */

    Route::get('users-teams', [HelperController::class, 'getTeamsAndUsers']);

    /*
     * ********** Teams *************
     * */
    Route::apiResource('teams', TeamController::class);
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
            Route::delete('/{balanceItem}', 'destroy')->middleware('check.project');;
        });
    });
    /* *
     * *********** Выберите или загрузите совокупность **********
     * */
    Route::group(['prefix' => 'aggregate'], function(){
        Route::controller(AggregateController::class)->group(function(){
            Route::get('/', 'index');
            Route::post( '/', 'store');
//            Route::post( '/td', 'storeTD');
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
     * TOC”s Балансовые статьи
     * */
    Route::group(['prefix' => 'balance-test'], function (){
        Route::controller(BalanceTestController::class)->group(function (){
            Route::get('/{project_id}', 'index');
            Route::post('/', 'store');
            Route::get('/show/{balanceTest}', 'show');
            Route::delete('/{balanceTest}', 'destroy');
            Route::get('/show/excel/{balanceTest}', 'excel');
        });
    });

    Route::post('balance-test/excel/download', [ExcelController::class, 'downloadBalance']);
    Route::post('income-test/excel/download', [ExcelController::class, 'downloadIncome']);

    /*
     * TOC”s Статья отчета о прибылях и убытках
     * */
    Route::group(['prefix' => 'income-test'], function (){
        Route::controller(IncomeTestController::class)->group(function (){
            Route::get('/{project_id}', 'index');
            Route::post('/', 'store');
            Route::get('/show/{incomeTest}', 'show');
            Route::delete('/{incomeTest}', 'destroy');
            Route::get('/show/excel/{incomeTest}', 'excel');
        });
    });
    /*
     * Результаты toc”s
     * */
    Route::group(['prefix' => 'tocs-result'], function (){
       Route::controller(ResultTocController::class)->group(function (){
           Route::get('/errors', 'errors');
           Route::post('/balance-error', 'balanceError');
           Route::post('/income-error', 'incomeError');
           Route::post('/income-comments', 'incomeComments');
           Route::post('/balance-comments', 'balanceComments');
       });
    });

    Route::group(['prefix' => 'td'], function (){
        Route::controller(TdController::class)->group(function (){
            Route::get('/balance', 'balanceTd');
            Route::get('/income', 'incomeTd');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::post('/{id}/matrix', 'storeMatrix');
            Route::get('/{id}/matrix', 'showMatrix');
            Route::get('/{id}/tocs', 'getTocsForTD');

        });
    });
    /*
     * Nature of control and frequency of performance
     * */
    Route::group(['prefix' => 'nature-control'], function (){
        Route::controller(NatureControlController::class)->group(function (){
            Route::get('/', 'index');
//            Route::post('/', 'store');
//            Route::get('/{balanceTest}', 'show');
        });
    });
});
