<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\ApiRegisterController;
use App\Http\Controllers\ApiConfirmRegistrationController;
use App\Http\Controllers\ApiSendNewCodeController;
use App\Http\Controllers\ApiRestorePasswordController;
use App\Http\Controllers\ApiInviteController;
use App\Http\Controllers\KidController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\KidParentController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Middleware\DenyDemoUser;

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

Route::prefix('v1')
    ->group(function(){
        /**
         * tokens api
         */
        // Route::prefix('auth')->middleware(['denyDemoUser'])
        Route::prefix('auth')->middleware(['throttle:check_code','denyDemoUser'])
        ->group(function(){
            Route::withoutMiddleware([DenyDemoUser::class])->group(function(){
                Route::post('/token', [TokenController::class, 'authToken']);
            });
            Route::post('/register',[ApiRegisterController::class, 'register']);
            Route::post('/confirm_registration',[ApiConfirmRegistrationController::class, 'confirmRegistration']);
            Route::post('/new_code',[ApiSendNewCodeController::class, 'createCode']);
            Route::post('/restore_password',[ApiRestorePasswordController::class, 'restorePassword']);
            Route::middleware(['auth:sanctum'])
            ->group(function() {
                Route::withoutMiddleware([DenyDemoUser::class])->group(function(){
                    Route::get('/user', [TokenController::class,'user']);
                });
                Route::get('/tokens', [TokenController::class,'getTokens']);
                Route::post('/tokens', [TokenController::class, 'createToken']);
                Route::delete('/tokens',[TokenController::class, 'revokeAllTokens']);
                Route::delete('/tokens/{id}',[TokenController::class, 'revokeToken']);
                Route::post('/invite',[ApiInviteController::class, 'invite']);
            });
        });

        /**
         * reports
         */
        Route::prefix('report')
        ->group(function(){
            Route::get('/public/{slug}/{period_id?}', PublicReportController::class);
        });


        Route::middleware(['auth:sanctum'])
        ->group(function () {
            Route::get('/organizations',[OrganizationController::class,'index']);
            Route::get('/organizations/{id}',[OrganizationController::class,'show']);
            Route::get('/kids',[KidController::class,'index']);
            Route::get('/kids/{id}',[KidController::class,'show']);
            Route::get('/periods',[PeriodController::class,'index']);
            Route::get('/periods/{id}',[PeriodController::class,'show']);
            Route::get('/kid_parents',[KidParentController::class,'index']);
            Route::get('/kid_parents/{id}',[KidParentController::class,'show']);
            Route::get('/payments',[PaymentController::class,'index']);
            Route::get('/payments/{id}',[PaymentController::class,'show']);
            Route::get('/operations',[OperationController::class,'index']);
            Route::get('/operations/{id}',[OperationController::class,'show']);
            Route::get('/plans',[PlanController::class,'index']);
            Route::get('/plans/{id}',[PlanController::class,'show']);
        });

        /**
         * basic api
         */
        // Route::middleware(['validation'])
        Route::middleware(['auth:sanctum','validation','denyDemoUser'])
        ->group(function () {
            Route::withoutMiddleware([DenyDemoUser::class])->group(function(){
                Route::get('/{table}',[TableController::class,'index']);
                Route::get('/{table}/{id}',[TableController::class,'show']);
            });
            Route::post('/{table}/{id?}',[TableController::class,'store']);
            Route::put('/{table}/{id}',[TableController::class,'update']);
            Route::patch('/{table}/{id}',[TableController::class,'update']);
            Route::delete('/{table}/{id}',[TableController::class,'delete']);
        });
    })
;
