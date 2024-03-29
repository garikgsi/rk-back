<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RegisterException;
use Illuminate\Auth\Events\Registered;
use App\Facades\UserApiRegistration;
use App\Events\ApiUserRegisterd;


class ApiRegisterController extends Controller
{
    public function register(Request $request) {
        if (config('school.onlyInviteRegister')) {
            return response()->formatApi([
                'error' => 'Регистрация только по приглашению зарегистрированных пользователей. Попросите зарегистрированного пользователя отправить Вам приглашение.'
            ], 403);
        } else {
            $validator = Validator::make($request->input(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Rules\Password::defaults()], // password == password_confirmation
            ],
            [
                'email.unique' => "Пользователь с электронной почтой :input уже зарегистрирован"
            ],
            [
                'name' => 'Имя',
                'email' => 'Электронная почта',
                'password' => 'Пароль'
            ]);

            if ($validator->fails()) {
                $formattedError = implode(' ',$validator->errors()->all());
                throw new RegisterException($formattedError, 422);
            } else {
                $userData = $validator->validated();
                $user = UserApiRegistration::create($userData['name'],$userData['email'],$userData['password']);

                event(new ApiUserRegisterd($user));

                return response()->formatApi([
                    'data' => $user
                ], 201);
            }
        }
    }
}
