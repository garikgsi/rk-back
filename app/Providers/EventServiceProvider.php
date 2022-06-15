<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CodeGenerated;
use App\Listeners\UserNewCodeNotification;
use App\Events\ApiUserRegisterd;
use App\Events\Invited;
use App\Listeners\ApiUserRegister;
use App\Listeners\SendInvite;
// use App\Observers\PeriodObserver;
// use App\Models\Period;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CodeGenerated::class => [
            UserNewCodeNotification::class,
        ],
        ApiUserRegisterd::class => [
            ApiUserRegister::class
        ],
        Invited::class => [
            SendInvite::class
        ],
    ];

    // /**
    //  * Наблюдатели моделей вашего приложения.
    //  *
    //  * @var array
    //  */
    // protected $observers = [
    //     Period::class => [PeriodObserver::class],
    // ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Period::observe(PeriodObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
