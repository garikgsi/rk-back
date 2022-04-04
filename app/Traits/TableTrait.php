<?php

/**
 * Trait for extends Model methods implemented TableInterface
 *
 */
namespace App\Traits;

use Illuminate\Support\Str;

trait TableTrait {

    /**
     * db table name
     *
     * @param  mixed $table
     * @return string
     */
    public function table():string
    {
        return $this->table ?: Str::camel(Str::singular(class_basename($this::class)));
    }

    /**
     * title of table
     *
     * @return string
     */
    public function title(): string
    {
        return  property_exists($this, 'title') ? $this->title : $this->table();
    }

}
