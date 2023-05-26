<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
            'period_id' => 'integer',
            'start_bill_date' => 'date|required',
            'kid_id' => 'integer|nullable'
        ],
        'messages' => [],
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'float',
        'amount' => 'float',
        'period_id' => 'integer',
    ];

    /**
     * __construct
     *
     * @param array $attributes
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
            TableModel::newField('start_bill_date')->setTitle('Дата начала учета')->setType('date')->save(),
            TableModel::newField('kid_id')->setTitle('Обучающийся')->setType('select')->save(),
        ]);
        $this->setGuarded([]);

    }

    // relations

    /**
     * plan period
     *
     * @return void
     */
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * operations by expenditure item
     *
     * @return void
     */

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    /**
     * plan for kid
     *
     * @return void
     */
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    /**
     * kid fio attribute
     */
    protected function kidFio(): Attribute
    {
        return new Attribute(
            get: function () {
                $kid = Kid::withTrashed()->find($this->kid_id);
                return $kid ? $kid->fio : '';
            }
        );
    }

    /**
     * kids studied in period
     * */
    public function kids()
    {
        return $this->period->kids()
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('start_study')->whereNull('end_study');
                })->orWhere(function ($query) {
                    $query->where(function ($query) {
                        $query->whereNotNull('start_study')->whereDate('start_study', '<=', $this->start_bill_date)->whereDate('start_study', '<=', $this->period->end_date);
                    });
                })
                    ->orWhere(function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('end_study')->whereDate('end_study', '>=', $this->start_bill_date)->whereDate('end_study', '<=', $this->period->end_date);
                        });
                    });
            })//            ->dd()
            ;
    }
}









