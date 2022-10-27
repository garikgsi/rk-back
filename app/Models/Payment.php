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


class Payment extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;

    protected $title = 'Оплаты';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'date_payment' => 'date|required',
            'comment' => 'string',
            'amount' => 'numeric|required|min:0',
            'kid_id' => 'integer|nullable',
            'period_id' => 'integer'
        ],
        'messages' => [],
    ];


    protected $casts = [
        'amount' => 'float',
        'kid_id' => 'integer',
        'period_id' => 'integer',
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
            TableModel::newField('date_payment')->setTitle('Дата поступления')->setType('date')->save(),
            TableModel::newField('comment')->setTitle('Комментарий')->fillable()->setType('string')->save(),
            TableModel::newField('amount')->setTitle('Сумма')->setType('money')->save(),
            TableModel::newField('kid_id')->setTitle('Ребенок')->setType('select')->save(),
            TableModel::newField('period_id')->setTitle('Период')->setType('select')->save(),
        ]);
        $this->setGuarded([]);
    }

    // relations
    /**
     * for kid payment
     *
     * @return void
     */
    public function kid() {
        return $this->belongsTo(Kid::class);
    }
    /**
     * payment for period
     *
     * @return void
     */
    public function period() {
        return $this->belongsTo(Period::class);
    }

}