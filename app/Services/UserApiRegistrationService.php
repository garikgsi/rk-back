<?php

namespace App\Services;

use App\Exceptions\UserException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;

class UserApiRegistrationService {
    /**
     * current user
     *
     * @var \App\Models\User $user
     */
    protected User $user;

    /**
     * register new user (invite) by registered user
     *
     * @param  User $user
     * @param  string $email
     * @param  string $name
     * @return User
     */
    public function inviteUser(User $user, string $email, string $name):User {
        $this->user = $user;
        return $this->create(name:$name, email:$email, password:Str::random(10), invited_by:$user->id);
    }

    /**
     * find user by email
     *
     * @param  string $email
     * @return \App\Models\User
     */
    public function findByEmail($email):User {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new UserException('Не удалось найти такого пользователя', 404);
        } else {
            $this->user = $user;
            return $user;
        }
    }

    /**
     * create new user
     *
     * @param  string $name
     * @param  string $email
     * @param  string $password
     * @return User
     */
    public function create($name, $email, $password, $invited_by=null):User {
        $userData = [
            'email' => $email,
            'name' => $name,
            'password' => $this->hashPassword($password),
            'invited_by' => $invited_by,
        ];
        try {
            $user = User::create($userData);
            $this->user = $user;
            return $this->setNewCode($user);
        } catch (Exception $e) {
            throw new UserException('Не удалось создать пользователя', 404);
        }
    }

    /**
     * set new confirmation code
     *
     * @return User
     */
    public function setNewCode(User $user):User {
        $code_expired = Carbon::now()->addMinutes(30);
        $user->code = rand(10000, 99999);
        $user->code_expired = $code_expired;
        try {
            $user->save();
            return $user;
        } catch (Exception $e) {
            throw new UserException('Не удалось создать код подтверждения', 500);
        }
    }

    /**
     * clear confirmation code
     *
     * @return void
     */
    public function clearCode(User $user):bool {
        $user->code = null;
        $this->user->code_expired = null;
        return $user->save() ? true : false;
    }

    /**
     * check confirmation code
     *
     * @param  User $user
     * @param  string $code
     * @return void
     */
    public function checkCode(User $user, string $code) {
        if (!is_null($user->code) && !is_null($user->code_expired) && $user->code == $code) {
            if (Carbon::createFromFormat('Y-m-d H:i:s',$user->code_expired)->lte(Carbon::now())) {
                throw new UserException('Код просрочен',403);
            }
        } else {
            throw new UserException('Неверный код',403);
        }
    }

    /**
     * change password for user identified by email
     *
     * @param  string $email
     * @param  string $code
     * @param  string $password
     * @return User
     */
    public function changePassword(string $email, string $code, string $password):User {
        $user = $this->findByEmail($email);
        $this->checkCode($user, $code);
        $user->password = $this->hashPassword($password);
        $user->save();
        $this->clearCode($user);
        return $user;
    }

    /**
     * return hashed password
     *
     * @param  mixed $password
     * @return void
     */
    public function hashPassword($password) {
        return Hash::make($password);
    }

    /**
     * atemmpt password with hashed password
     *
     * @param  mixed $password
     * @return void
     */
    public function attemptPassword($password, $hash) {
        return Hash::check($password, $hash);
    }
}
