<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RegisterException;
use App\Facades\UserApiRegistration;
use App\Events\Invited;

class ApiInviteController extends Controller
{
    public function invite(Request $request) {
        $validator = Validator::make($request->input(),
        [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ],
        [],
        [
            'name' => 'ФИО пользователя',
            'email' => 'Электронная почта',
        ]);
        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new RegisterException($formattedError, 422);
        } else {
            $userData = $validator->validated();
            // check existed user
            try {
                // user exists
                $user = UserApiRegistration::findByEmail(email:$userData['email']);
                return response()->formatApi(['data' => $user], 200);
            } catch (\Throwable $th) {
                // created user
                $invitedUser = UserApiRegistration::inviteUser(user:$request->user(), name:$userData['name'] ,email:$userData['email']);
                event(new Invited($invitedUser));
                return response()->formatApi(['data' => $invitedUser], 201);
            }
        }
    }
}
