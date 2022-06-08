<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\User;

class TableExceptionTest extends TestCase
{
    protected $table = "_wrong_table_";

    // /**
    //  * test show view when exception
    //  *
    //  * @return void
    //  */
    // public function test_web_exception() {
    //     $response = $this->get("/$this->table");
    //     $response->assertNotFound()
    //         ->assertSeeText("Таблица $this->table не найдена в описании моделей");
    // }

    /**
     * test json response same format when api
     *
     * @return void
     */
    public function test_json_exception() {
        $user = User::whereNotNull('email_verified_at')->get()->random();
        $response = $this->actingAs($user)->getJson("/api/v1/$this->table");
        $response->assertNotFound()
            ->assertJson(function (AssertableJson $json){
                $json->where('error', "Таблица $this->table не найдена в описании моделей")
                    ->where('is_error', true)
                    ->etc();
            });

    }
}
