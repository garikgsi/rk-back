<?php

/**
 * Trait for extends Model methods implemented TableInterface
 *
 */
namespace App\Traits;

use Exception;
use Illuminate\Support\Str;
use App\Services\TableClasses\TableField;

use App\Facades\TableModel;
use App\Services\TableClasses\TableModel as TableFields;

trait TableTrait {

    protected TableFields $fields;

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
     * getter eloquent model class
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this::class;
    }

    /**
     * title of table
     *
     * @return string
     */
    public function title(): string
    {
        return property_exists($this, 'title') ? $this->title : $this->table();
    }

    /**
     * get validation rules based on protected property $validation
     *
     * @param  string $mode
     * @return array
     */
    public function validationRules(string $mode='store'): array
    {
        try {
            return $this->validation['rules'];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * get validation error messages based on protected property $validation
     *
     * @param  string $mode
     * @return array
     */
    public function validationMessages(string $mode='store'): array
    {
        try {
            return $this->validation['messages'];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * get field names for correct output errors on validation
     *
     * @return array
     */
    public function validationNames(): array
    {
        return TableModel::getValidationNames();
    }

    /**
     * set fields model for table
     *
     * @param  mixed $model
     * @return void
     */
    public function setFields($model) {
        $defaultFields = [
            TableModel::newField('id')->setTitle('id')->setType('number')->save(),
            TableModel::newField('deleted_at')->setTitle('Дата удаления')->setType('datetime')->save(),
            TableModel::newField('created_at')->setTitle('Дата создания')->setType('datetime')->save(),
            TableModel::newField('updated_at')->setTitle('Дата обновления')->setType('datetime')->save(),
        ];
        $this->fields = TableModel::addMany(array_merge($defaultFields, $model));
    }

    /**
     * get fields model
     *
     * @return App\Services\TableClasses\TableModel
     */
    public function getFields():TableFields
    {
        return $this->fields;
    }

    /**
     * get field from model by $name
     *
     * @param  string $name
     * @return ?App\Services\TableClasses\TableField
     */
    public function getField(string $name): ?TableField
    {
        return $this->fields->getField($name);
    }

    /**
     * merge protected property $guarded
     *
     * @param  array $fields
     * @return void
     */
    public function setGuarded(array $fields) {
        $this->guarded = array_merge(['id', 'deleted_at', 'created_at', 'updated_at'], $fields);
    }

}
