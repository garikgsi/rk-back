<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\ApiRegisterController;
use App\Http\Controllers\ApiConfirmRegistrationController;
use App\Http\Controllers\ApiSendNewCodeController;
use App\Http\Controllers\ApiRestorePasswordController;


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
        Route::prefix('auth')->middleware(['throttle:check_code'])
        ->group(function(){
            Route::post('/token', [TokenController::class, 'authToken']);
            Route::post('/register',[ApiRegisterController::class, 'register']);
            Route::post('/confirm_registration',[ApiConfirmRegistrationController::class, 'confirmRegistration']);
            Route::post('/new_code',[ApiSendNewCodeController::class, 'createCode']);
            Route::post('/restore_password',[ApiRestorePasswordController::class, 'restorePassword']);
            Route::middleware('auth:sanctum')
            ->group(function() {
                Route::get('/user', [TokenController::class,'user']);
                Route::get('/tokens', [TokenController::class,'getTokens']);
                Route::post('/tokens', [TokenController::class, 'createToken']);
                Route::delete('/tokens',[TokenController::class, 'revokeAllTokens']);
                Route::delete('/tokens/{id}',[TokenController::class, 'revokeToken']);
            });
        });

        /**
         * reports
         */
        Route::prefix('report')
        ->group(function(){
            Route::get('/public/{period_id?}', PublicReportController::class);
        });

        /**
         * basic api
         */
        // Route::middleware(['validation'])
        Route::middleware(['auth:sanctum','validation'])
        ->group(function () {
            Route::get('/{table}',[TableController::class,'index']);
            Route::post('/{table}/{id?}',[TableController::class,'store']);
            Route::get('/{table}/{id}',[TableController::class,'show']);
            Route::put('/{table}/{id}',[TableController::class,'update']);
            Route::patch('/{table}/{id}',[TableController::class,'update']);
            Route::delete('/{table}/{id}',[TableController::class,'delete']);
        });
    })
;
