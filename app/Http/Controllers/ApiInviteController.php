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
            'email' => 'required|string|email|max:255|unique:users',
        ],
        [
            'email.unique' => 'Пользователь c электронной почтой :input уже приглашен'
        ],
        [
            'name' => 'ФИО пользователя',
            'email' => 'Электронная почта',
        ]);
        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new RegisterException($formattedError, 422);
        } else {
            $userData = $validator->validated();
            $invitedUser = UserApiRegistration::inviteUser(user:$request->user(), name:$userData['name'] ,email:$userData['email']);
            event(new Invited($invitedUser));
            return response()->formatApi([], 201);
        }
    }
}
