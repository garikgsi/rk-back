<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;

class ApiGetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;


    /**
     * simple get request
     *
     * @return void
     */
    public function test_get_users()
    {
        $response = $this->request('users');
        $response->assertStatus(200);
    }

    /**
     * request user by id
     *
     * @return void
     */
    public function test_get_user_by_id()
    {
        $user = User::get()->random();
        $url = "users/$user->id";
        $response = $this->request($url);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($user) {
                $json->where('data.id', $user->id)
                    ->where('data.name', $user->name)
                    ->etc();
            });
    }

    /**
     * not existed user in table
     *
     * @return void
     */
    public function test_not_exister_row()
    {
        $user_id = User::max('id')+1;
        $url = "users/$user_id";
        $response = $this->request($url);
        $response->assertStatus(404);
    }

    /**
     * request not existed table
     *
     * @return void
     */
    public function test_wrong_table()
    {
        $url = "_not_existed_table_";
        $response = $this->request($url);
        $response->assertStatus(404)
            ->assertJson(function (AssertableJson $json) use ($url){
                $json->where('error', "Таблица $url не найдена в описании моделей")
                    ->where('is_error', true);
            });
    }

    /**
     * test deny to unauthorized user
     *
     * @return void
     */
    // public function test_unauthorized()
    // {
    //     $response = $this->get("/api/v1/users");
    //     $response->assertStatus(403);
    // }

    /**
     * return admin user for request
     *
     * @return User
     */
    protected function adminUser(): User
    {
        return User::whereNotNull('email_verified_at')->first();
    }


    protected function request($url)
    {
        return $this->actingAs($this->adminUser())->getJson("/api/v1/$url");
    }
}
