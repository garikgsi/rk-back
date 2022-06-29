<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Facades\UserApiRegistration;
use Exception;
use App\Exceptions\UserException;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ApiUserRegistered;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;
use App\Notifications\RegCode;
use App\Events\ApiPasswordReset;
use App\Events\Invited;
use App\Notifications\InviteNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;



class ApiRegistrationServiceTest extends TestCase
{
    /**
     * test find real user by email
     *
     * @return void
     */
    public function testFindByEmailRealUser()
    {
        $user = User::factory()->create();
        $findedUser = UserApiRegistration::findByEmail($user->email);
        $this->assertSame($user->id, $findedUser->id);
    }

    /**
     * error when try to search unexisted user
     *
     * @return void
     */
    public function testFindByEmailUnrealUser() {
        try {
            UserApiRegistration::findByEmail('_some@unreal.email');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof UserException);
        }
    }

    /**
     * test register new user
     *
     * @return App\Models\User
     */
    public function testRegister():User {
        Notification::fake();
        $url = 'auth/register';
        $email = 'test_register@example.email';
        $postData = [
            'name' => 'someTestUser',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(201);
        $newUser = UserApiRegistration::findByEmail($email);
        $response->assertJson(function (AssertableJson $json) use($newUser) {
            $json->has('data')
            ->where('is_error', false)
            ->where('data.id',$newUser->id)
            ->etc();
        });
        Notification::assertSentTo(
            [$newUser], function (ApiUserRegistered $notification) use ($newUser) {
                return $notification->user->email === $newUser->email && $notification->user->code==$newUser->code;
            }
        );

        $this->assertTrue(Auth::attempt(['email'=>$email, 'password'=>'password']));

        return $newUser;
    }

    /**
     * test register new user whithout valid credentions
     *
     * @return void
     */
    public function testRegisterWithoutValidCredentions() {
        $url = 'auth/register';
        $postData = [
            'name' => 'someTestUser',
            'password' => 'password',
            'password_confirmation' => 'password2'
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(422);
        $response->assertJson([
            'is_error'=> true
        ]);
    }

    /**
     * error when try to create user by eloquent
     *
     * @return void
     */
    public function testErrorCreationUser() {
        $existedEmail = User::get()->random()->email;
        try {
            UserApiRegistration::create(name:'test', email: $existedEmail, password:'password');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof UserException);
        }
    }

    /**
     * test check code function
     *
     * @return void
     */
    public function testCreateAndCheckCode() {
        $user = User::factory()->create();
        // check empty code (empty by default)
        try {
            UserApiRegistration::checkCode($user, '56778');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof UserException);
            $this->assertSame('Неверный код', $e->getMessage());
            $this->assertSame(403, $e->getCode());
        }
        UserApiRegistration::setNewCode($user);
        // check wrong code
        try {
            UserApiRegistration::checkCode($user, intval($user->code)+1);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof UserException);
            $this->assertSame('Неверный код', $e->getMessage());
            $this->assertSame(403, $e->getCode());
        }
        // check expired code
        $this->travel(45)->minutes();
        try {
            UserApiRegistration::checkCode($user, $user->code);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof UserException);
            $this->assertSame('Код просрочен', $e->getMessage());
            $this->assertSame(403, $e->getCode());
        }
        // check real code
        $this->travelBack();
        try {
            UserApiRegistration::checkCode($user, $user->code);
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * test confirmation email with wrong credentions
     *
     * @depends testRegister
     * @return void
     */
    public function testConfirRegistrationWithWrongCredentions($user) {
        $url = 'auth/confirm_registration';
        $postData = [
            'email' => $user->email,
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(422);
    }

    /**
     * test confirmation email when user registered
     *
     * @depends testRegister
     * @return void
     */
    public function testConfirmRegistration($user) {
        Event::fake();
        $url = 'auth/confirm_registration';
        $postData = [
            'email' => $user->email,
            'code' => $user->code
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json){
            $json->has('data.id')
            ->has('data.token')
            ->where('is_error', false)
            ->where('error',null)
            ->etc();
        });
        Event::assertDispatched(Verified::class);
    }

    /**
     * test confirm registration when email has verified
     *
     * @return void
     */
    public function testConfirmConfirmedUser() {
        Event::fake();

        $user = User::whereNotNull('email_verified_at')->get()->random();
        // set new code
        UserApiRegistration::setNewCode($user);
        $url = 'auth/confirm_registration';
        $postData = [
            'email' => $user->email,
            'code' => (string)$user->code
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json){
            $json->where('is_error', true)
            ->where('error','Регистрация уже подтверждена')
            ->etc();
        });
        Event::assertNotDispatched(Verified::class);
    }

    /**
     * get new code for existed user
     *
     * @return void
     */
    public function testGetNewCode() {
        Notification::fake();

        $user = User::get()->random();
        $oldCode = $user->code;
        $url = 'auth/new_code';
        $postData = [
            'email' => $user->email,
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(201);
        $response->assertJson([
            'is_error' => false,
            'error' => null
        ]);
        $newCode = User::find($user->id)->code;
        $this->assertTrue((string)$oldCode !== (string)$newCode);
        Notification::assertSentTo(
            [$user], function (RegCode $notification) use ($newCode, $user) {
                return $notification->user->email === $user->email && $notification->user->code==$newCode;
            }
        );
    }

    /**
     * get new code for unreal user
     *
     * @return void
     */
    public function testGetNewCodeForUnrealUser() {
        Notification::fake();

        $url = 'auth/new_code';
        $postData = [
            'email' => 'some_fake_email@example.test',
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(404);
        $response->assertJson([
            'is_error' => true,
            'error' => 'Не удалось найти такого пользователя'
        ]);
        Notification::assertNothingSent();
    }

    /**
     * test doent send new code when incorrect credentions
     *
     * @return void
     */
    public function testGetNewCodeWithoutCorrectCredentions() {
        Notification::fake();

        $url = 'auth/new_code';
        $postData = [
            'email' => 'some_wrong_email',
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(422);
        $response->assertJson([
            'is_error' => true,
        ]);
        Notification::assertNothingSent();
    }

    /**
     * expects error 422 wrong credentions
     *
     * @return void
     */
    public function testErrorRestorePasswordWithWrongCredentions() {
        Event::fake();
        $url = 'auth/restore_password';
        $postData = [
            'email' => 'some_wrong_email',
            'code' => '65654'
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(422);
        $response->assertJson([
            'is_error' => true,
        ]);

        Event::assertNotDispatched(ApiPasswordReset::class);
    }

    /**
     * expects error 404 wrong user
     *
     * @return void
     */
    public function testErrorRestorePasswordWithWrongUser() {
        Event::fake();
        $url = 'auth/restore_password';
        $postData = [
            'email' => 'test_error_restore@example.user',
            'code' => '65654',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(404);
        $response->assertJson([
            'is_error' => true,
            'error' => 'Не удалось найти такого пользователя'
        ]);

        Event::assertNotDispatched(ApiPasswordReset::class);
    }

    /**
     * expects error 403 wrong code
     *
     * @return App\Models\User
     */
    public function testErrorRestorePasswordWithWrongCode():User {
        Event::fake();
        $user = User::get()->random();
        $user->email_verified_at = null;
        $user->code = '56789';
        $user->code_expired = Carbon::now();
        $user->save();
        $url = 'auth/restore_password';
        $postData = [
            'email' => $user->email,
            'code' => '56789',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(403);
        $response->assertJson([
            'is_error' => true,
            'error' => 'Код просрочен'
        ]);

        Event::assertNotDispatched(ApiPasswordReset::class);
        return $user;
    }

    /**
     * expects error 404 wrong user
     *
     * @depends testErrorRestorePasswordWithWrongCode
     * @return void
     */
    public function testRestorePassword($user) {
        Event::fake();
        $url = 'auth/restore_password';
        $newPassword = Str::random(10);
        $postData = [
            'email' => $user->email,
            'code' => $user->code,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ];
        // back to 10 min
        $this->travel(-10)->minutes();
        $response = $this->request($url, 'post', $postData);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json){
            $json->has('data.id')
            ->has('data.token')
            ->where('is_error', false)
            ->where('error',null)
            ->etc();
        });

        Event::assertDispatched(ApiPasswordReset::class);
        $newUser = User::find($user->id);
        $this->assertTrue(UserApiRegistration::attemptPassword($newPassword, $newUser->password));
        $this->assertTrue(is_null($newUser->code));
        $this->assertTrue(Auth::attempt(['email'=>$user->email, 'password'=>$newPassword]));
    }

    /**
     * expect error 422 when invited registered user
     *
     * @return void
     */
    public function testInviteExistedUser() {
        Event::fake();
        $user = User::get()->random();
        $url = 'auth/invite';
        $postData = [
            'name' => 'Invited User',
            'email' => $user->email,
        ];
        $response = $this->request($url, 'post', $postData, true);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) use ($user){
            $json->where('is_error', false)
            ->where('data.id',$user->id)
            ->etc();
        });

        Event::assertNotDispatched(Invited::class);
    }

    /**
     * expect error 422 when invited with invalid credentions
     *
     * @return void
     */
    public function testInviteUserWithInvalidCredentions() {
        Event::fake();
        $url = 'auth/invite';
        $postData = [
            'email' => 'wrong@email',
        ];
        $response = $this->request($url, 'post', $postData, true);
        $response->assertStatus(422);
        $response->assertJson(function (AssertableJson $json){
            $json->where('is_error', true)
            ->etc();
        });

        Event::assertNotDispatched(Invited::class);
    }

    /**
     * expect code 201 when invited user is OK
     *
     * @return void
     */
    public function testInviteUser() {
        Notification::fake();
        $url = 'auth/invite';
        $email = 'test_invite_user@example.mail';
        $postData = [
            'email' => $email,
            'name' => 'Invited User'
        ];
        $response = $this->request($url, 'post', $postData, true);
        $response->assertStatus(201);
        $newUser = User::where('email',$email)->first();
        Notification::assertSentTo(
            [$newUser], function (InviteNotification $notification) use ($newUser) {
                return $notification->user->email === $newUser->email && $notification->user->code==$newUser->code;
            }
        );
    }

    /**
     * request
     *
     * @param  string $url
     * @return TestResponse
     */
    protected function request($url, $method='get', $data=null, $withAuthUser=false):TestResponse
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
