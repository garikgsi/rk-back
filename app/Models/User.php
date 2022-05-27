<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Interfaces\TableInterface;
use App\Traits\TableTrait;
use App\Traits\TableFilterTrait;
use App\Facades\TableModel;
use App\Traits\TableOrderLimitsTrait;


class User extends Authenticatable implements TableInterface, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, TableTrait, TableFilterTrait, TableOrderLimitsTrait;


    protected $title = 'Пользователи';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'invited_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'code',
        'code_expired',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'code_expired' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setFields([
            TableModel::newField('name')->setTitle('Имя')->fillable()->setType('string')->save(),
            TableModel::newField('email')->setTitle('Email')->fillable()->setType('email')->save(),
            TableModel::newField('password')->setTitle('Пароль')->fillable()->setType('password')->save(),
        ]);

    }

    // relations
    /**
     * parent related this account
     *
     * @return void
     */
    public function kidParent() {
        return $this->hasOne(KidParent::class);
    }

}
