<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Facades\Table;
use App\Exceptions\TableException;
use App\Models\Message;
use Exception;

class TableRepositoryTest extends TestCase
{
    /**
     * test getting Model name by table
     *
     * @return void
     */
    public function test_get_model()
    {
        $model = Table::use('users')->getModel();
        $this->assertSame(class_basename($model),'User');
    }

    /**
     * test unconstructing repo usage
     *
     * @return void
     */
    public function test_use_repository_without_init()
    {
        try {
            $model = Table::getModel();
        } catch (Exception $e) {
            $this->assertTrue($e instanceof(TableException::class));
        }
        $this->assertTrue(!isset($model));
    }

    public function test_get_all_data()
    {
        $data = Table::use('messages')->getAll();
        $this->assertSame($data->pluck('id')->values()->all(), Message::get()->pluck('id')->values()->all());
    }

    public function test_get_all_ids()
    {
        $data = Table::use('messages')->getIds();
        $this->assertSame(Message::get()->pluck('id')->values()->all(), $data);
    }

}
