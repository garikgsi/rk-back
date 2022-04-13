<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TokenController;


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
        Route::prefix('auth')
        ->group(function(){
            Route::middleware('auth:sanctum')
            ->group(function() {
                Route::get('/user', [TokenController::class,'user']);
                Route::get('/tokens', [TokenController::class,'getTokens']);
                Route::post('/tokens', [TokenController::class, 'createToken']);
                Route::delete('/tokens',[TokenController::class, 'revokeAllTokens']);
                Route::delete('/tokens/{id}',[TokenController::class, 'revokeToken']);
            });
            Route::post('/token', [TokenController::class, 'authToken']);
        });
        /**
         * basic api
         */
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
