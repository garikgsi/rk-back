<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RegisterException;
use Illuminate\Validation\Rules;
use App\Facades\UserApiRegistration;
use App\Events\ApiPasswordReset;


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
                event(new ApiPasswordReset($user));
                return response()->formatApi([], 200);
            } else {
                return response()->formatApi([
                    'data' => null,
                    'error' => "Пароль не изменен. Попробуйте повторить запрос позднее."
                ], 500);
            }
        }
    }
}
