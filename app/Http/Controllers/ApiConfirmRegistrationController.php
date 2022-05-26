<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\RegisterException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Verified;
use App\Facades\Token;
use App\Facades\UserApiRegistration;

class ApiConfirmRegistrationController extends Controller
{
    public function confirmRegistration(Request $request) {
        $validator = Validator::make($request->input(),
        [
            'email' => 'required|string|email',
            'code' => 'required|string'
        ],
        [],
        [
            'email' => 'Электронная почта',
            'code' => 'Код'
        ]);

        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new RegisterException($formattedError, 422);
        } else {
            $confirmData = $validator->validated();

            $user = UserApiRegistration::findByEmail($confirmData['email']);
            if ($user->hasVerifiedEmail()) {
                return response()->formatApi([
                    'data' => null,
                    'error' => "Регистрация уже подтверждена"
                ], 200);
            } else {
                UserApiRegistration::checkCode($user, $confirmData['code']);
                $user->markEmailAsVerified();
                if (UserApiRegistration::clearCode($user)) {
                    event(new Verified($user));
                    // return token if verified
                    return response()->formatApi([
                        'data' => Token::createUserToken($user,'registration')
                    ]);
                }
            }
        }
    }
}
