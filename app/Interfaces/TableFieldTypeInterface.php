<?php

namespace App\Interfaces;

use App\Services\TableClasses\TableField;
use Illuminate\Database\Eloquent\Builder;

interface TableFieldTypeInterface {
    public function filter(Builder $builder, TableField $field, $filters);
}
