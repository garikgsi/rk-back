<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use App\Facades\Token;


class TokenController extends Controller
{
    /**
     * get user auth by token
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function user(Request $request): Response
    {
        return response()->formatApi([
            'data'=>Token::getUser($request)
        ]);
    }

    /**
     * return tokens for current user
     *
     * @param  Illuminate\Http\Request $request
     * @return Response
     */
    public function getTokens(Request $request): Response
    {
        return response()->formatApi(Token::userTokens($request));
    }

    /**
     * create new token for current auth user
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function createToken(Request $request): Response
    {
        return response()->formatApi([
            'data'=>Token::create($request)
        ],201);
    }

    /**
     * auth user by email/password and return new token if login & password correct
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function authToken(Request $request): Response
    {
        $tokens = Token::auth($request);
        return response()->formatApi([
            'data'=>$tokens
        ],201);
    }

    /**
     * revoke all user tokens
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function revokeAllTokens(Request $request): Response
    {
        return response()->formatApi([
            Token::revokeAllTokens($request)
        ], 204);
    }

    /**
     * revoke token with $id
     *
     * @param  Illuminate\Http\Request $request
     * @param  int|string $id
     * @return Illuminate\Http\Response
     */
    public function revokeToken(Request $request, int|string $id): Response
    {
        return response()->formatApi([
            Token::revokeToken($request, (int)$id)
        ], 204);
    }
}
