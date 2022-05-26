<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RegisterException;
use App\Facades\UserApiRegistration;
use App\Events\CodeGenerated;

class ApiSendNewCodeController extends Controller
{
    public function createCode(Request $request) {
        $validator = Validator::make($request->input(),
        [
            'email' => 'required|string|email',
        ],
        [],
        [
            'email' => 'Электронная почта',
        ]);
        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new RegisterException($formattedError, 422);
        } else {
            $email = $validator->validated()['email'];
            $user = UserApiRegistration::findByEmail($email);
            if (UserApiRegistration::setNewCode($user)) {
                event(new CodeGenerated($user));
                return response()->formatApi([], 201);
            } else {
                return response()->formatApi([
                    'data' => null,
                    'error' => "Код не создан. Попробуйте повторить запрос позднее."
                ], 500);
            }
        }
    }
}
