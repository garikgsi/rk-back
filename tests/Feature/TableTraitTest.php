<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use App\Models\TestModel;

class TableTraitTest extends TestCase
{
    /**
     * test get title for model with setted property
     *
     * @return void
     */
    public function test_get_title_with_prop()
    {
        $user = new User();
        $this->assertSame($user->title(),'Пользователи');
    }

    /**
     * test get title for model without setted property
     *
     * @return void
     */
    public function test_get_title_without_prop()
    {
        $message = new Message();
        $this->assertSame($message->title(),'message');
    }

    /**
     * test return empty rules where prop $reles not set
     *
     * @return void
     */
    public function test_empty_rules()
    {
        $testModel = new TestModel();
        $this->assertSame([],$testModel->validationRules());
        $this->assertSame([],$testModel->validationMessages());
    }

    public function test_get_model_class()
    {
        $message = new Message();
        $this->assertSame($message->getModelClass(),'App\\Models\\Message');

    }

}
