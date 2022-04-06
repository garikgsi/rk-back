<?php

namespace App\Services\TableClasses;

/**
 * TableField type class
 */
class TableFieldType {
    protected $fieldType;

    public function __construct($fieldType) {
        $this->fieldType = $fieldType;
        return $this;
    }
}
