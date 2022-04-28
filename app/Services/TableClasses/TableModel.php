<?php

namespace App\Services\TableClasses;

use App\Services\TableClasses\TableField;
use App\Services\TableClasses\TableFieldBuilder;
use App\Services\TableClasses\TableModel as TableFields;

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
     * check that field exists in model (search by name)
     *
     * @param  string $name
     * @return bool
     */
    public function has(string $name):bool {
        foreach($this->fields as $field) {
            if ($field->getName()==$name) {
                return true;
            }
        }
        return false;
    }

    /**
     * add few fields to model
     *
     * @param  array $fields
     * @return self
     */
    public function addMany(array $fields):self {
        foreach ($fields as $field) {
            if (!$this->has($field->getName())) $this->fields[] = $field;
        }
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
     * @return TableFields
     */
    public function getFields(): self {
        return $this;
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

    /**
     * getter field from model (search by name)
     *
     * @param  string $name
     * @return ?TableField
     */
    public function getField(string $name): ?TableField {
        foreach($this->fields as $field) {
            if ($field->getName()==$name) {
                return $field;
            }
        }
        return null;
    }


}
