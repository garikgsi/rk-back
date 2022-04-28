<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Facades\TableModel;
use App\Services\TableClasses\TableModel as TableFields;

class TableModelTest extends TestCase
{
    /**
     * test add one field to model
     *
     * @return void
     */
    public function testAddOneField() {
        $field = TableModel::newField('test')->setTitle('Just a test')->fillable()->setType('string')->save();
        TableModel::add($field);
        $this->assertSame(TableModel::getField('test'),$field);
        $this->assertSame($field->getType(),'string');
    }

    /**
     * return null if field doesn't exist
     *
     * @return void
     */
    public function testUnexistedField() {
        $this->assertSame(TableModel::getField('not_existed_field_name'),null);
    }

    /**
     * return TableModel instance
     *
     * @return void
     */
    public function testReturnFieldsModel() {
        TableModel::newField('test')->setTitle('Just a test')->fillable()->setType('string')->save();
        TableModel::newField('test2')->setTitle('Just a test2')->fillable()->setType('string')->save();
        TableModel::newField('test3')->setTitle('Just a test3')->fillable()->setType('string')->save();
        $this->assertTrue(TableModel::getFields() instanceof TableFields);
    }

}
