<?php

namespace App\Services\TableClasses;

use App\Services\TableClasses\TableFieldType;
use App\Services\TableClasses\TableFieldBuilder;

/**
 * table field class
 */

class TableField {
    /**
     * field name
     *
     * @var string
     */
    protected string $name;
    /**
     * field title
     *
     * @var  string
     */
    protected string $title;
    /**
     * fillable field property
     *
     * @var  bool
     */
    protected bool $fillable;
    /**
     * default value for field
     *
     * @var  mixed
     */
    protected mixed $defaultValue;
    /**
     * type of field
     *
     * @var  TableFieldType
     */
    protected TableFieldType $valueType;

    /**
     * construct field by builder
     *
     * @param  App\Services\TableClasses\TableFieldBuilder $builder
     * @return void
     */
    public function __construct(TableFieldBuilder $builder)
    {
        $this->name = $builder->name;
        $this->title = $builder->title;
        $this->fillable = $builder->fillable;
        $this->defaultValue = $builder->defaultValue;
        $this->valueType = $builder->valueType;
    }

    /**
     * getter field name
     *
     * @return string
     */
    public function getName():string {
        return $this->name;
    }

    /**
     * getter field title
     *
     * @return string
     */
    public function getTitle():string {
        return $this->title;
    }
}
