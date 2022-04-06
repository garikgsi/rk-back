<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TableClasses\TableModel;

class TableModelProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TableModel::class, function ($app){
            return new TableModel();
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('tableModel', TableModel::class);
    }
}
