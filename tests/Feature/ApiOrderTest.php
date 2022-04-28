<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use App\Models\Message;


class ApiOrderTest extends TestCase
{

    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * test single sorting
     *
     * @return void
     */
    public function testSingleDescSorting() {
        $url = "messages?limit=10&sort=id.desc";
        $msg = Message::orderBy('id','desc')->get()->first();
        $response = $this->request($url);
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('error', null)
                    ->where('is_error', false)
                    ->has('data', 10, fn ($json) =>
                        $json->where('id', $msg->id)
                            ->etc()
                )->etc()
            );
    }
    /**
     * test multiple sorting
     *
     * @return void
     */
    public function testMultipleSorting() {
        $url = "messages?limit=10&sort=id.asc,cost,message.desc";
        $msg = Message::orderBy('id')->orderBy('cost')->orderBy('message','desc')->get()->first();
        $response = $this->request($url);
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('error', null)
                    ->where('is_error', false)
                    ->has('data', 10, fn ($json) =>
                        $json->where('id', $msg->id)
                            ->etc()
                )->etc()
            );
    }
    /**
     * test offset and default sorting by id asc
     *
     * @return void
     */
    public function testOffset() {
        $url = "messages?limit=10&offset=5";
        $msg = Message::orderBy('id')->skip(5)->take(10)->get()->first();
        $response = $this->request($url);
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('error', null)
                    ->where('is_error', false)
                    ->has('data', 10, fn ($json) =>
                        $json->where('id', $msg->id)
                            ->etc()
                )->etc()
            );
    }
    /**
     * test paginator
     *
     * @return void
     */
    public function testPaginator() {
        $url = "messages?rows=5&page=3&sort=cost.desc";
        $msg = Message::orderBy('cost','desc')->skip(10)->take(5)->get()->first();
        $response = $this->request($url);
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('error', null)
                    ->where('is_error', false)
                    ->has('data', 5, fn ($json) =>
                        $json->where('id', $msg->id)
                            ->etc()
                )->etc()
            );
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
     * request get
     *
     * @param  string $url
     * @return TestResponse
     */
    protected function request($url):TestResponse
    {
        return $this->actingAs($this->adminUser())->getJson("/api/v1/$url");
    }

}
