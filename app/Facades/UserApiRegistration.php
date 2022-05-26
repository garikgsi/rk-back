<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\UserApiRegistrationService
 */
class UserApiRegistration extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'user_api_registration';
    }
}
