<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use Illuminate\Testing\TestResponse;

class ApiPostTest extends TestCase
{

    /**
     * test create new row
     *
     * @return void
     */
    public function testCreate() {
        $url = "messages";
        $data = [
            'message' => 'some text _test_my_text',
            'is_translit' => true,
            'number' => '+7(933)345-45-54'
        ];
        $response = $this->request($url, $data);
        $response->assertStatus(201)
            ->assertJson([
                'is_error' => false,
                'error' => null,
                'data' => $data
            ]);
    }

    /**
     * test copy existed row
     *
     * @return void
     */
    public function testCopyRow() {
        $message = Message::get()->random();
        $url = "messages/$message->id";
        $message_data = [
            'is_translit' => $message->is_translit,
            'number' => $message->number
        ];
        $data = [
            'message' => 'another text message'
        ];
        $response = $this->request($url, $data);
        $response->assertStatus(201)
            ->assertJson([
                'is_error' => false,
                'error' => null,
                'data' => array_merge($message_data, $data)
            ]);
    }

    /**
     * test copy deleted row
     *
     * @return void
     */
    public function testCopyDeletedRow() {
        $message = Message::get()->random();
        $messageId = $message->id;
        Message::find($messageId)->forceDelete();
        $url = "messages/$messageId";
        $data = [
            'message' => 'deleted text message'
        ];
        $response = $this->request($url, $data);
        $response->assertStatus(404)
            ->assertJson([
                'is_error' => true,
                'error' => "Не удалось извлечь запись с id=$messageId из таблицы message",
            ]);
    }

    /**
     * test copy row with invalid data
     *
     * @return void
     */
    public function testCopyInvalidData() {
        $message = Message::get()->random();
        $messageId = $message->id;
        $url = "messages/$messageId";
        $data = [
            'number' => 'wrong number format'
        ];
        $response = $this->request($url, $data);
        $response->assertStatus(422)
            ->assertJson([
                'is_error' => true,
            ]);
    }

    /**
     * return admin user for request
     *
     * @return User
     */
    protected function adminUser(): User
    {
        return User::whereNotNull('email_verified_at')->first();
    }


    /**
     * request put/patch
     *
     * @param  string $url
     * @param  array $data
     * @return TestResponse
     */
    protected function request($url, $data):TestResponse
    {
        return $this->actingAs($this->adminUser())->postJson("/api/v1/$url", $data);
    }
}
