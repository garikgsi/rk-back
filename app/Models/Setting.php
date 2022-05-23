<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Facades\TableModel;
use App\Traits\TableFilterTrait;
use App\Traits\TableOrderLimitsTrait;
use App\Interfaces\TableInterface;
use App\Traits\TableTrait;


class Setting extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;

    protected $title = 'Настройки';

    /**
     * validation rules
     *
     * @var  array
     */
    protected $validation = [
        'rules' => [
            'name' => 'string|unique:messages|required|min:3|max:255',
            'title' => 'string|nullable',
            'value' => 'json|required',
        ],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'json',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setFields([
            TableModel::newField('name')->setTitle('Настройка')->fillable()->setType('string')->save(),
            TableModel::newField('title')->setTitle('Описание')->fillable()->setType('string')->save(),
            TableModel::newField('value')->setTitle('Значение')->fillable()->setType('string')->save(),
        ]);
    }

    public function check($name, $value) {
        return $this->where('name',$name)->where('value',$value)->count()==1;
    }

}
