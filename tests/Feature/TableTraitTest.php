<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;

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
}
