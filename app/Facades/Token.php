<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\TokenService
 */
class Token extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tokens';
    }
}
