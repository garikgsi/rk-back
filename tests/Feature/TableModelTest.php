<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Facades\TableModel;

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
        $this->assertSame(TableModel::getFields(),[$field]);
    }

}
