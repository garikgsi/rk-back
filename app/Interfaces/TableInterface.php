<?php

/**
 * Table interface implemented Laravel Model instance
 */
namespace App\Interfaces;

use App\Services\TableClasses\TableField;
use App\Services\TableClasses\TableModel;

interface TableInterface {
    /**
     * table name in database
     *
     * @return string
     */
    public function table():string;
    /**
     * title of table
     *
     * @return string
     */
    public function title(): string;
    /**
     * get validation rules based on protected property $validation
     *
     * @param  string $mode
     * @return array
     */
    public function validationRules(string $mode): array;
    /**
     * get validation error messages based on protected property $validation
     *
     * @param  string $mode
     * @return array
     */
    public function validationMessages(string $mode): array;
    /**
     * get field names for correct output errors on validation
     *
     * @return array
     */
    public function validationNames(): array;
    /**
     * set fields model for table
     *
     * @param  mixed $model
     * @return void
     */

    public function setFields(array $fields);
    /**
     * merge protected property $guarded
     *
     * @param  array $fields
     * @return void
     */

    public function setGuarded(array $fields);
    /**
     * get fields model
     *
     * @return App\Services\TableClasses\TableModel
     */

    public function getFields():TableModel;
    /**
     * get field from model by $name
     *
     * @param  string $name
     * @return ?App\Services\TableClasses\TableField
     */
    public function getField(string $name): ?TableField;

    public function getModelClass():string;
}
