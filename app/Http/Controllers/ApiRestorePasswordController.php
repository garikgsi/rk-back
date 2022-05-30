<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RegisterException;
use Illuminate\Validation\Rules;
use App\Facades\UserApiRegistration;
use App\Events\ApiPasswordReset;
use App\Facades\Token;

class ApiRestorePasswordController extends Controller
{
    public function restorePassword(Request $request) {
        $validator = Validator::make($request->input(),
        [
            'email' => 'required|string|email',
            'code' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // password == password_confirmation
        ],
        [],
        [
            'code' => 'Код восстановления',
            'email' => 'Электронная почта',
            'password' => 'Пароль'
        ]);

        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new RegisterException($formattedError, 422);
        } else {
            $userData = $validator->validated();
            $user = UserApiRegistration::changePassword(email:$userData['email'], code:$userData['code'], password:$userData['password']);
            if ($user) {
                // for invited users set verified email
                if (!is_null($user->invited_by)) $user->markEmailAsVerified();
                event(new ApiPasswordReset($user));
                $responseData = array_merge($user->toArray(), [
                    'token' => Token::createUserToken($user)
                ]);
                return response()->formatApi([
                    'data'=>$responseData
                ], 200);
            } else {
                return response()->formatApi([
                    'data' => null,
                    'error' => "Пароль не изменен. Попробуйте повторить запрос позднее."
                ], 500);
            }
        }
    }
}
