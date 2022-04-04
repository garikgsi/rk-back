<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * API response formatter
         * @var array $result
         * @var int $code
         * @return Illuminate\Http\Response
         */
        Response::macro('formatApi', function(array $result, int $code=200)
        {
            $res = [
                'is_error' => false,
                'error' => null
            ];
            if (isset($result['error'])) {
                $res = array_merge($res, [
                    'is_error' => true,
                    'error' => $result['error']
                ]);
            }
            if (isset($result['data'])) {
                $res = array_merge($res, [
                    'data' => $result['data']
                ]);
            }
            if (isset($result['count'])) {
                $res = array_merge($res, [
                    'count' => $result['count']
                ]);
            }
            return Response::make($res, $code);
        });
    }
}
