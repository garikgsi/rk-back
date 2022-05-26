<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UserApiRegistrationService;

class UserApiRegistrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('user_api_registration', UserApiRegistrationService::class);
    }
}
