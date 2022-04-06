<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;

class ApiUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * put test
     *
     * @return void
     */
    public function testPutRequest() {
        $message = Message::get()->random();
        $url = "messages/$message->id";
        $data = [
            'message' => 'some text _test_my_text',
            'is_translit' => true,
            'number' => '+7(925)222-22-34'
        ];
        $response = $this->request($url, 'put', $data);
        $response->assertStatus(200)
            ->assertJson([
                'data' => $data,
                'is_error' => false,
                'error' => null
            ]);
    }

    /**
     * patch test
     *
     * @return void
     */
    public function testPatchRequest() {
        $message = Message::get()->random();
        $url = "messages/$message->id";
        $data = [
            'message' => 'some text _test_my_text',
            'is_translit' => !(bool)$message->is_translit,
        ];
        $response = $this->request($url, 'patch', $data);
        $response->assertStatus(200)
            ->assertJson([
                'data' => array_merge($data,['number'=>$message->number]),
                'is_error' => false,
                'error' => null
            ]);

    }

    /**
     * validation test on put request
     *
     * @return void
     */
    public function testValidationErrorOnPut() {
        $message = Message::get()->random();
        $url = "messages/$message->id";
        $data = [
            'message' => 'some text _test_my_text',
        ];
        $response = $this->request($url, 'put', $data);
        $response->assertStatus(422)
            ->assertJson([
                'is_error' => true,
            ]);
    }

    /**
     * validation test on patch request
     *
     * @return void
     */
    public function testValidationErrorOnPatch() {
        $message = Message::get()->random();
        $url = "messages/$message->id";
        $data = [
            'message' => 'some text _test_my_text',
        ];
        $response = $this->request($url, 'patch', $data);
        $response->assertStatus(200)
            ->assertJson([
                'data' => array_merge($data,['number'=>$message->number]),
                'is_error' => false,
                'error' => null
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
    protected function request($url, $method, $data):TestResponse
    {
        return $this->actingAs($this->adminUser())->{$method."Json"}("/api/v1/$url", $data);
    }

}
