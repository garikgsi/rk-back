<?php

namespace App\Models;

use App\Casts\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Interfaces\TableInterface;
use App\Facades\TableModel;
use App\Traits\FileableTable;
use App\Traits\TableFilterTrait;
use App\Traits\TableOrderLimitsTrait;
use App\Traits\TableTrait;


class Operation extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait, FileableTable;

    protected $title = 'Расходные операции';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'date_operation' => 'date|required',
            'comment' => 'string|required',
            'price' => 'numeric|required|min:0',
            'quantity' => 'numeric|required|min:1',
            'amount' => 'numeric|required|min:0',
            'image' => 'nullable|image|mimes:jpg,bmp,png',
            'check_url' => 'nullable|url',
            'plan_id' => 'nullable|integer',
            'period_id' => 'integer'
        ],
        'messages' => [
            'image.mimes' => 'Чек/накладная должен быть в формате jpg,bmp,png'
        ],
    ];

    protected $casts = [
        'image' => File::class,
        'price' => 'float',
        'quantity' => 'float',
        'amount' => 'float',
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
            TableModel::newField('date_operation')->setTitle('Дата расходной операции')->setType('date')->save(),
            TableModel::newField('comment')->setTitle('Комментарий')->fillable()->setType('string')->save(),
            TableModel::newField('price')->setTitle('Стоимость')->setType('money')->save(),
            TableModel::newField('quantity')->setTitle('Количество')->setType('money')->save(),
            TableModel::newField('amount')->setTitle('Сумма')->setType('money')->save(),
            TableModel::newField('image')->setTitle('Чек/накладная')->setType('image')->save(),
            TableModel::newField('check_url')->setTitle('Ссылка на чек/накладную')->fillable()->setType('string')->save(),
            TableModel::newField('plan_id')->setTitle('Статья расходов')->setType('select')->save(),
            TableModel::newField('period_id')->setTitle('Период')->setType('select')->save(),
        ]);
        $this->setGuarded([]);
    }

    // relations
    /**
     * expenditure item
     *
     * @return void
     */
    public function plan() {
        return $this->belongsTo(Plan::class);
    }

    /**
     * operation for period
     *
     * @return void
     */
    public function period() {
        return $this->belongsTo(Period::class);
    }

}
