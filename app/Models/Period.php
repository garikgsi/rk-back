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


class Period extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;

    protected $title = 'Периоды';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'name' => 'string|required',
            'start_date' => 'date',
            'end_date' => 'date',
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
            TableModel::newField('name')->setTitle('Обозначение периода')->fillable()->setType('string')->save(),
            TableModel::newField('start_date')->setTitle('Дата начала периода')->setType('date')->save(),
            TableModel::newField('end_date')->setTitle('Дата окончания периода')->setType('date')->save(),
        ]);
        $this->setGuarded([]);

    }

    // relations
    /**
     * plans on period
     *
     * @return void
     */
    public function plans() {
        return $this->hasMany(Plan::class);
    }
    /**
     * plans on period
     *
     * @return void
     */
    public function operations() {
        return $this->hasMany(Operation::class);
    }
    /**
     * plans on period
     *
     * @return void
     */
    public function payments() {
        return $this->hasMany(Payment::class);
    }


}
