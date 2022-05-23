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


class Kid extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;


    protected $title = 'Дети';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'last_name' => 'string|required',
            'name' => 'string|required',
            'patronymic' => 'nullable|string',
            'birthday' => 'nullable|date',
            'start_study' => 'nullable|date',
            'end_study' => 'nullable|date',
        ],
        'messages' => [],
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
            TableModel::newField('birthday')->setTitle('Дата рождения')->setType('date')->save(),
            TableModel::newField('start_study')->setTitle('Начало обучения')->setType('date')->save(),
            TableModel::newField('end_study')->setTitle('Конец обучения')->setType('date')->save(),
        ]);
        $this->setGuarded([]);
    }

    // relations
    /**
     * kid parents
     *
     * @return void
     */
    public function parents() {
        return $this->hasMany(KidParent::class);
    }

    /**
     * payments for kid
     *
     * @return void
     */
    public function payments() {
        return $this->hasMany(Payment::class);
    }

}
