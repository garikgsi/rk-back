<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @see \App\Services\TableClasses\TableModel
 */
class TableModel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tableModel';
    }
}
