<?php

namespace App\Models;

use App\Interfaces\TableInterface;
use App\Traits\TableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Facades\TableModel;

class Message extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait ;

    // protected $title = 'Сообщения';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'message' => 'string|required|min:3|max:255',
            'is_translit' => 'boolean|required',
            'number' => 'string|required|regex:/^\+7\(?\d{3}\)?\d{3}\-\d{2}\-\d{2}$/i',
        ],
        'messages' => [
            'number.regex' => 'Номер телефона не соответствует формату +7(ХХХ)ХХХ-ХХ-ХХ'
        ],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'is_translit' => 'boolean',
    ];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setFields([
            TableModel::newField('message')->setTitle('Текст сообщения')->fillable()->setType('string')->save(),
            TableModel::newField('is_translit')->setTitle('Транслителировать')->fillable()->setType('bool')->setDefault(false)->save(),
            TableModel::newField('number')->setTitle('Номер телефона')->fillable()->setType('phone')->save(),
            TableModel::newField('cost')->setTitle('Стоимость')->setType('money')->save(),
            TableModel::newField('sent_at')->setTitle('Дата отправки')->setType('datettime')->save(),
        ]);

        $this->setGuarded(['cost', 'sent_at']);

    }

}
