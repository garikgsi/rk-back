<?php

namespace App\Services;

use App\Exceptions\TableException;
use App\Interfaces\TableInterface;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Main Service repository for manipulate data
 */
class TableRepositoryService {

    /**
     * table name in API call
     */
    protected string $table;

    /**
     * Model implemented custom TableInterface
     *
     */
    protected $model=null;

    /**
     * className of Model, like App\Models\User
     *
     * @var undefined
     */
    protected $modelClass = null;



    /**
     * __construct
     *
     * @param  string $table
     * @return void
     */
    public function __construct(string $table='')
    {
        if ($table !== '') $this->use($table);
    }

    /**
     * alternative constructor for Facade usage
     *
     * @param  string $table
     * @return TableRepositoryService
     */
    public function use(string $table) : TableRepositoryService | null
    {
        $modelClass = $this->convertTableToModel($table);
        if (class_exists($modelClass)) {
            $this->table = $table;
            $this->model = new $modelClass;
        } else {
            throw new TableException("Таблица $table не найдена в описании моделей", 404);
        }
        return $this;
    }

    /**
     * convert url table to Laravel Model with namespace
     *
     * @param  mixed $table
     * @return string
     */
    protected function convertTableToModel(string $table)
    {
        return 'App\\Models\\'.Str::singular(Str::studly($table));
    }

    /**
     * getter Model
     *
     * @param  mixed $table
     * @return bool
     */
    public function getModel(): TableInterface | null
    {
        $this->checks();
        return $this->model;
    }

    /**
     * show rows from table
     *
     * @param  Request $request
     * @return array
     */
    public function show(Request $request):array
    {
        $this->checks();
        return $this->model->get()->take($request->limit)->toArray();
    }

    /**
     * find row identified by $id and return it
     *
     * @param  string $table
     * @param  int $id
     * @return TableInterface
     */
    public function find(string $table, int $id): TableInterface
    {
        $this->checks();
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            if ($e instanceof NotFoundHttpException) {
                throw new TableException("Запись с id=$id не найдена таблице ".$this->model->title()."", 404);
            } else {
                throw new TableException("Не удалось извлечь запись с id=$id из таблицы ".$this->model->title()."", 404);
            }
        }
    }

    /**
     * update row identified by $id
     *
     * @param  Illuminate\Http\Reques $request
     * @param  string $table
     * @param  int $id
     * @return TableInterface
     */
    public function update(Request $request, string $table, int $id): TableInterface
    {
        $this->checks();
        $rules = $this->model->validationRules('update');
        // for patch request validate only requested fields
        if (strtolower($request->method())=='patch') {
            $newRules = [];
            foreach($rules as $field=>$rule) {
                if ($request->has($field)) $newRules[$field] = $rule;
            }
            $rules = $newRules;
        }
        $validator = Validator::make($request->input(),
            $rules,
            $this->model->validationMessages('update'),
            $this->model->validationNames()
        );
        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new TableException($formattedError, 422);
        } else {
            $row = $this->find($table, $id);
            $row->fill($validator->validated())->save();
            return $row;
        }
    }

    /**
     * insert new or copy existed row
     *
     * @param  Illuminate\Http\Reques $request
     * @param  string $table
     * @param  int|string|null $id
     * @return TableInterface
     */
    public function store(Request $request, string $table, int|string|null $id): TableInterface
    {
        $this->checks();
        $rules = $this->model->validationRules('store');
        // replace only requested fields in copied row
        if ($id!=null) {
            $newRules = [];
            foreach($rules as $field=>$rule) {
                if ($request->has($field)) $newRules[$field] = $rule;
            }
            $rules = $newRules;
        }
        $validator = Validator::make($request->input(),
            $rules,
            $this->model->validationMessages('store'),
            $this->model->validationNames()
        );

        if ($validator->fails()) {
            $formattedError = implode(' ',$validator->errors()->all());
            throw new TableException($formattedError, 422);
        } else {
            if ($id==null) {
                // new row
                $this->model->fill($validator->validated())->save();
                $row = $this->model;
            } else {
                // copy existed row
                $row = $this->find($table, $id)->replicate();
                $row->fill($validator->validated());
                $row->save();
            }
            return $row;
        }
    }

    /**
     * delete table row identified by $id
     *
     * @param  Illuminate\Http\Reques $request
     * @param  string $table
     * @param  int $id
     * @return bool
     */
    public function delete(Request $request, string $table, int $id):bool {
        $this->checks();
        $row = $this->find($table, $id);
        return $row->delete();
    }

    /**
     * check if repository is initialized and class implementes such interfaces
     *
     * @return void
     */
    protected function checks()
    {
        if ($this->model==null || $this->table=='') {
            throw new TableException("Репозиторий не идентифицирован", 421);
        }
        $modelInterfaces = class_implements($this->model);
        if (!isset($modelInterfaces['App\Interfaces\TableInterface'])) {
            throw new TableException("Модель не может реализовать необходимые методы", 421);
        }
    }
}
