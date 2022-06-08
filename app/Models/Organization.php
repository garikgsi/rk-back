<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Interfaces\TableInterface;
use App\Facades\TableModel;
use App\Scopes\OrganizationScope;
use App\Traits\TableFilterTrait;
use App\Traits\TableOrderLimitsTrait;
use App\Traits\TableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class Organization extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait, TableFilterTrait, TableOrderLimitsTrait;

    protected $title = 'Учреждения';

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
            TableModel::newField('title')->setTitle('Обозначение')->fillable()->setType('string')->save(),
            TableModel::newField('slug')->setTitle('Ссылка')->fillable()->setType('string')->save(),
            TableModel::newField('admin_id')->setTitle('Администратор')->setType('select')->save(),

        ]);
        $this->setGuarded([]);
    }

    // relations
    /**
     * kids
     *
     * @return void
     */
    public function kids() {
        return $this->hasMany(Kid::class);
    }

    /**
     * periods
     *
     * @return void
     */
    public function periods() {
        return $this->hasMany(Period::class);
    }

    /**
     * all organization payments
     *
     * @return void
     */
    public function payments() {
        return $this->hasManyThrough(Payment::class, Period::class);
    }

    /**
     * all organization plans
     *
     * @return void
     */
    public function plans() {
        return $this->hasManyThrough(Plan::class, Period::class);
    }

    /**
     * all organization plans
     *
     * @return void
     */
    public function parents() {
        return $this->hasManyThrough(KidParent::class, Kid::class);
    }

    /**
     * validation rules specified by request type
     *
     * @param  mixed $mode
     * @return array
     */
    public function validationRules(string $mode='store'): array
    {
        switch ($mode) {
            case 'update': {
                return [
                    'title' => 'string|sometimes|required',
                    'slug' => [
                        'string',
                        'sometimes',
                        Rule::unique('organizations')->ignore($this->id),
                    ],
                    'admin_id' => 'integer|required'
                ];
            }
            case 'store': default: {
                return [
                    'title' => 'string|required',
                    'slug' => 'string|required|unique:App\Models\Organization',
                    'admin_id' => 'integer|required'
                ];
            } break;
        }
    }

    /**
     * return if user is admin of organization
     *
     * @return void
     */
    public function isAdmin()
    {
        $user = Auth::user();
        if (isset($this->id) && isset($this->admin_id) && $user) {
            return $this->admin_id == $user->id || Organization::where('id',$this->id)->whereHas('kids', function($kids) use ($user) {
                $kids->whereHas('parents', function($parents) use ($user) {
                    $parents->where('is_admin', 1)->where('user_id', $user->id);
                });
            })->count()>0;
        }
        return false;
    }
}
