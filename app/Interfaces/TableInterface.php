<?php

/**
 * Table interface implemented Laravel Model instance
 */
namespace App\Interfaces;

interface TableInterface {

    /**
     * table name in database
     *
     * @return string
     */
    public function table():string;


}
