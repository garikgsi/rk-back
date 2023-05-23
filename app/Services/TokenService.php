<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\User;
use App\Exceptions\TokenException;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class TokenService
{
    /**
     * get current auth user
     *
     * @param  Illuminate\Http\Request $request
     * @return App\Models\User
     */
    public function getUser(Request $request): User
    {
        return $request->user();
    }

    /**
     * create new token for current auth user
     *
     * @param  Illuminate\Http\Request $request
     * @return string
     */
    public function create(Request $request): string
    {
        $token = $request->user()->createToken($request->has('token_name') ? $request->token_name : 'simple token');
        return $token->plainTextToken;
    }

    /**
     * create new token for user
     *
     * @param  User $user
     * @param  string $tokenName
     * @return string
     */
    public function createUserToken(User $user, string $tokenName='simple token'): string
    {
        $token = $user->createToken($tokenName);
        return $token->plainTextToken;
    }

    /**
     * auth credentions and get new token if login/password are valid
     *
     * @param  Illuminate\Http\Request $request
     * @return string
     */
    public function auth(Request $request): string
    {
        $validator = Validator::make($request->all(),
        [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ],[],
        [
            'email' => 'Логин',
            'password' => 'Пароль',
            'device_name' => 'Устройство',
        ]);

        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new TokenException($formattedError, 422);
        } else {
            $userData = $validator->validated();
            $user = User::where('email', $userData["email"])->first();
            if ($user) {
                if (Auth::attempt(['email'=>$userData["email"], 'password'=>$userData["password"]])) {
                    if (!$user->hasVerifiedEmail() ) {
                        throw new TokenException("Электронная почта не была подтверждена", 403);
                    } else {
                        return $user->createToken($userData["device_name"])->plainTextToken;
                    }
                }
            }
            throw new TokenException("Неверная связка логин-пароль", 401);
        }
    }

    /**
     * get all tokens for current auth user
     *
     * @param  Illuminate\Http\Request $request
     * @return array
     */
    public function userTokens(Request $request): array
    {
        $tokens = $request->user()->tokens()->get();
        $count = $tokens->count();
        $pageLimit = $request->has('limit') ? $request->limit : 10;
        $pageNum = $request->has('page') ? $request->page : 1;
        $tokens = $tokens->skip($pageLimit*($pageNum-1))->take($pageLimit);
        return ['data'=>$tokens->values()->toArray(), 'count'=>$count];
    }

    /**
     * revoke all current user tokens
     *
     * @param  Illuminate\Http\Request $request
     * @return bool
     */
    public function revokeAllTokens(Request $request): bool
    {
        return $request->user()->tokens()->delete();
    }

    /**
     * revoke token with $id
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $id
     * @return bool
     */
    public function revokeToken(Request $request, int $id): bool
    {
        try {
            $token = $request->user()->tokens()->findOrFail($id);
            return $token->delete();
        } catch (Exception $e) {
            throw new TokenException("Токен $id не найден", 404);
        }
    }
}
