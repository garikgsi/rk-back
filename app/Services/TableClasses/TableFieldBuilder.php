<?php

namespace App\Services\TableClasses;

/**
 * builder for TableField
 */

class TableFieldBuilder {
    public string $name;
    public string $title;
    public bool $fillable = false;
    public mixed $defaultValue = null;
    public string $valueType;


    /**
     * constructor - create field builder with name
     *
     * @param  string $name
     * @return void
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * setter title
     *
     * @param  string $title
     * @return TableFieldBuilder
     */
    public function setTitle(string $title):self {
        $this->title = $title;
        return $this;
    }

    /**
     * setter fillable
     *
     * @param  mixed $fillable
     * @return TableFieldBuilder
     */
    public function fillable(bool $fillable=true):self {
        $this->fillable = $fillable;
        return $this;
    }

    /**
     * setter defaultValue
     *
     * @param  mixed $defaultValue
     * @return TableFieldBuilder
     */
    public function setDefault($defaultValue):self {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * setter fieldType
     *
     * @param  string $valueType
     * @return TableFieldBuilder
     */
    public function setType($valueType):self {
        $this->valueType = $valueType;
        return $this;
    }

    /**
     * save builded field
     *
     * @return TableField
     */
    public function save() : TableField {
        return new TableField($this);
    }

}
