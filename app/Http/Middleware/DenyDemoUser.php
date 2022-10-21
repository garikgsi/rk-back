<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DenyDemoUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('app.isDevelop')===false) {
            $user = $request->user();
            if ($user) {
                if ($request->user()->email=='demo@example.com') {
                    return response()->formatApi([
                        'error' => 'Данная операция запрещена для демо-пользователя'
                    ], 421);
                }
            } else {
                if ($request->has('email') && $request->email == 'demo@example.com') {
                    return response()->formatApi([
                        'error' => 'Невозможно обработать демо-запрос'
                    ], 421);
                }
            }
        }

        return $next($request);
    }
}