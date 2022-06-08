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
            'organization_id' => 'integer|nullable',
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
            TableModel::newField('organization_id')->setTitle('Учреждение')->setType('select')->save(),
        ]);
        $this->setGuarded([]);
    }

    // relations

    /**
     * organization where kid studies
     *
     * @return void
     */
    public function organization() {
        return $this->belongsTo(Organization::class);
    }

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


    // helpers
    /**
     * return if user is admin of  organization belongs period
     *
     * @return bool
     */
    public function isAdmin():bool {
        $user = Auth::user();
        if ($this->organization && $user) {
            return $this->organization->admin_id == $user->id || Organization::where('id',$this->organization_id)->whereHas('kids', function($kids) use ($user) {
                $kids->whereHas('parents', function($parents) use ($user) {
                    $parents->where('is_admin', 1)->where('user_id', $user->id);
                });
            })->count()>0;
        }
        return false;
    }

}
