<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use Illuminate\Testing\TestResponse;

class ApiDeleteTest extends TestCase
{

    /**
     * test delete existed row
     *
     * @return void
     */
    public function testDeleteRow() {
        $message = Message::get()->random();
        $url = "messages/$message->id";
        $response = $this->request($url);
        $response->assertStatus(204);
    }

    /**
     * test delete deleted row
     *
     * @return void
     */
    public function testDeleteDeletedRow() {
        $message = Message::get()->random();
        $messageId = $message->id;
        $message->delete();
        $url = "messages/$messageId";
        $response = $this->request($url);
        $response->assertStatus(404)
        ->assertJson([
            'is_error' => true,
            'error' => "Не удалось извлечь запись с id=$messageId из таблицы message",
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
     * request delete
     *
     * @param  string $url
     * @param  array $data
     * @return TestResponse
     */
    protected function request($url):TestResponse
    {
        return $this->actingAs($this->adminUser())->deleteJson("/api/v1/$url");
    }
}
