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


class Plan extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;

    protected $title = 'Планирование';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'title' => 'string|required',
            'price' => 'numeric|required|min:0',
            'quantity' => 'numeric|required|min:1',
            'amount' => 'numeric|required|min:0',
            'period_id' => 'integer'
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
            TableModel::newField('title')->setTitle('Статья учета')->fillable()->setType('string')->save(),
            TableModel::newField('price')->setTitle('Стоимость')->setType('money')->save(),
            TableModel::newField('quantity')->setTitle('Количество')->setType('money')->save(),
            TableModel::newField('amount')->setTitle('Сумма')->setType('money')->save(),
            TableModel::newField('period_id')->setTitle('Период')->setType('select')->save(),
        ]);
        $this->setGuarded([]);

    }

    // relations
    /**
     * plan period
     *
     * @return void
     */
    public function period() {
        return $this->belongsTo(Period::class);
    }

    /**
     * operations by expenditure item
     *
     * @return void
     */
    public function operations() {
        return $this->hasMany(Operation::class);
    }
}
