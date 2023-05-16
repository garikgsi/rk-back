<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Interfaces\TableInterface;
use App\Facades\TableModel;
use App\Traits\TableFilterTrait;
use App\Traits\TableOrderLimitsTrait;
use App\Traits\TableTrait;
use Illuminate\Support\Facades\Auth;


class KidParent extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;


    protected $title = 'Родители';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'last_name' => 'string|required',
            'name' => 'string|required',
            'patronymic' => 'string|nullable',
            'phone' => 'string|nullable|max:30|regex:/^\+7\(?\d{3}\)?\d{3}\-\d{2}\-\d{2}$/i',
            'user_id' => 'integer|nullable',
            'kid_id' => 'integer',
            'is_admin' => 'boolean'
        ],
        'messages' => [
            'phone.regex' => 'Номер телефона не соответствует формату +7(ХХХ)ХХХ-ХХ-ХХ'
        ],
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'kid_id' => 'integer',
        'user_id' => 'integer'
    ];


    /**
     * __construct
     *
     * @param  array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setFields([
            TableModel::newField('last_name')->setTitle('Фамилия')->fillable()->setType('string')->save(),
            TableModel::newField('name')->setTitle('Имя')->fillable()->setType('string')->save(),
            TableModel::newField('patronymic')->setTitle('Отчество')->fillable()->setType('string')->save(),
            TableModel::newField('phone')->setTitle('Номер телефона')->fillable()->setType('phone')->save(),
            TableModel::newField('user_id')->setTitle('Учетная запись')->setType('select')->save(),
            TableModel::newField('kid_id')->setTitle('Ребенок')->setType('select')->save(),
            TableModel::newField('is_admin')->setTitle('В родительском комитете')->setType('boolean')->save(),
        ]);
        $this->setGuarded(['is_admin']);
    }

    // relations
    /**
     * kid
     *
     * @return void
     */
    public function kid() {
        return $this->belongsTo(Kid::class);
    }

    /**
     * user account
     *
     * @return void
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

}
