<?php

namespace App\Providers;

use App\Services\TableRepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class TableProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->bind(TableRepositoryService::class, function ($app){
        //     return new TableRepositoryService();
        // });
        $this->app->singleton(TableRepositoryService::class, function($app){
            return new TableRepositoryService($app->make(Request::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('table', TableRepositoryService::class);
    }
}
