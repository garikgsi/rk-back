<?php

namespace App\Services\TableClasses;

use App\Services\TableClasses\TableField;
use App\Services\TableClasses\TableFieldBuilder;

/**
 * service for manage table fields
 *
 */
class TableModel {

    /**
     * @var  array <TableField>
     */
    protected $fields = [];

    /**
     * add new field to model
     *
     * @param  TableField $field
     * @return self
     */
    public function add(TableField $field):self {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * add few fields to model
     *
     * @param  array $fields
     * @return self
     */
    public function addMany(array $fields):self {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     * helper - builder new field with name=$name
     *
     * @param  string $name
     * @return TableFieldBuilder
     */
    public function newField(string $name) : TableFieldBuilder {
        $builder = new TableFieldBuilder($name);
        return $builder;
    }

    /**
     * get all fields in model
     *
     * @return array
     */
    public function getFields(): array {
        return $this->fields;
    }

    /**
     * get array field names for translate validation errors
     *
     * @return array
     */
    public function getValidationNames():array {
        $res = [];
        foreach($this->fields as $field) {
            $res[$field->getName()] = $field->getTitle();
        }
        return $res;
    }

}
