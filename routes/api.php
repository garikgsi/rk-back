<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TableController;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            Route::middleware('auth:sanctum')->get('user', function (Request $request) {
                return $request->user();
            });
            Route::middleware('auth:sanctum')->post('/tokens/create', function (Request $request) {
                $token = $request->user()->createToken($request->has('token_name') ? $request->token_name : 'simple token');

                return ['token' => $token->plainTextToken];
            });
            Route::post('/token', function (Request $request) {
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required',
                    'device_name' => 'required',
                ]);

                $user = User::where('email', $request->email)->first();

                if (! $user || ! Hash::check($request->password, $user->password)) {
                    throw ValidationException::withMessages([
                        'email' => ['The provided credentials are incorrect.'],
                    ]);
                }

                return $user->createToken($request->device_name)->plainTextToken;
            });
        });
        /**
         * basic api
         */
        // Route::middleware(['auth:sanctum','validation'])
        Route::middleware(['validation'])
        ->group(function () {
            Route::get('/{table}',[TableController::class,'index']);
            Route::post('/{table}/{id?}',[TableController::class,'store']);
            Route::get('/{table}/{id}',[TableController::class,'show']);
            Route::put('/{table}/{id}',[TableController::class,'update']);
            Route::patch('/{table}/{id}',[TableController::class,'update']);
        });
    })
;
