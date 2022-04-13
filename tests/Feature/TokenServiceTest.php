<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;

class TokenServiceTest extends TestCase
{
    protected $user = null;

    /**
     * test get auth user
     *
     * @return void
     */
    public function testGetUser()
    {
        $url = "auth/user";
        $response = $this->request($url);
        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
                $json->where('data.id', $this->user->id)
                ->where('data.name', $this->user->name)
                ->where('is_error', false)
                ->etc();
            });
    }

    /**
     * test create token for auth user
     *
     * @return void
     */
    public function testCreateToken()
    {
        $url = "auth/tokens";
        $response = $this->request($url, 'post');
        $response->assertStatus(201);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('data')
            ->where('is_error', false)
            ->etc();
        });
    }

    /**
     * test error auth when wrong credentions
     *
     * @return void
     */
    public function testAuthWrongCredentions()
    {
        $url = 'auth/token';
        $postData = [
            'email' => 'some_invalid@email.test',
            'password' => '123w2w2w',
            'device_name' => 'device',
        ];
        $response = $this->request($url, 'post', $postData, false);
        $response->assertStatus(401)
            ->assertJson([
                'is_error' => true,
                'error'=>'Неверная связка логин-пароль'
            ]);
    }

    /**
     * test error auth without require field
     *
     * @return void
     */
    public function testAuthWrongData()
    {
        $url = 'auth/token';
        $postData = [
            'email' => 'some_invalid@email.test',
            'device_name' => 'device',
        ];
        $response = $this->request($url, 'post', $postData, false);
        $response->assertStatus(422)
            ->assertJson([
                'is_error' => true,
                'error'=>'Поле Пароль обязательно для заполнения.'
            ]);
    }

    /**
     * test normail auth
     *
     * @return void
     */
    public function testAuth()
    {
        $user = User::factory()->create();
        $url = 'auth/token';
        $postData = [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'device',
        ];
        $response = $this->request($url, 'post', $postData, false);
        $response->assertStatus(201);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('data')
            ->where('is_error', false)
            ->etc();
        });
    }

    /**
     * test get user tokens list
     *
     * @return void
     */
    public function testGetUserTokens()
    {
        $url = 'auth/tokens';
        $this->user = User::factory()->create();
        for($i=0; $i<rand(20,80); $i++) {
            $this->user->createToken('fake_device')->plainTextToken;
        }
        Sanctum::actingAs(
            $this->user,
            ['*']
        );

        $response = $this->request($url,'get',null,false);
        $tokens = $this->user->tokens()->get();
        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) use ($tokens) {
            $json->has('data')
            ->where('count',$tokens->count())
            ->where('is_error', false)
            ->etc();
        });
    }

    /**
     * test revoke unreal token
     *
     * @return void
     */
    public function testRevokeUnrealToken()
    {
        $this->user = User::factory()->create();
        for($i=0; $i<rand(20,80); $i++) {
            $this->user->createToken('fake_device')->plainTextToken;
        }
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $tokens = $this->user->tokens()->get();
        $tokenId = $tokens->max('id')+10000;

        $url = "auth/tokens/$tokenId";
        $response = $this->request($url,'delete',null,false);
        $response->assertStatus(404)
        ->assertJson([
            'is_error' => true,
            'error' => "Токен $tokenId не найден",
        ]);
    }

    /**
     * test revoke existed token
     *
     * @return void
     */
    public function testRevokeExistedToken()
    {
        $this->user = User::factory()->create();
        for($i=0; $i<rand(20,80); $i++) {
            $this->user->createToken('fake_device')->plainTextToken;
        }
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $tokens = $this->user->tokens()->get();
        $tokenId = $tokens->random()->id;

        $url = "auth/tokens/$tokenId";
        $response = $this->request($url,'delete',null,false);
        $response->assertStatus(204);
        $this->assertSame(0, $this->user->tokens()->where('id',$tokenId)->count());
    }

    /**
     * test revoke all tokens
     *
     * @return void
     */
    public function testRevokeAllTokens()
    {
        $this->user = User::factory()->create();
        for($i=0; $i<rand(20,80); $i++) {
            $this->user->createToken('fake_device')->plainTextToken;
        }
        Sanctum::actingAs(
            $this->user,
            ['*']
        );

        $url = "auth/tokens";
        $response = $this->request($url,'delete',null,false);
        $response->assertStatus(204);
        $this->assertSame(0, $this->user->tokens()->get()->count());
    }

    /**
     * request
     *
     * @param  string $url
     * @return TestResponse
     */
    protected function request($url, $method='get', $data=null, $withAuthUser=true):TestResponse
    {
        if ($withAuthUser) {
            $this->user = User::factory()->create();
            for($i=0; $i<rand(20,80); $i++) {
                $this->user->createToken('fake_device')->plainTextToken;
            }
            Sanctum::actingAs(
                $this->user,
                ['*']
            );
        }
        if ($data) {
            return $this->{$method."Json"}("/api/v1/$url", $data);
        } else {
            return $this->{$method."Json"}("/api/v1/$url");
        }
    }



}
