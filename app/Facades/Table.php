<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @see \App\Services\TableRepositoryService
 */
class Table extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'table';
    }
}
