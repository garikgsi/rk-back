<?php

namespace App\Services;

use App\Exceptions\TableException;
use App\Interfaces\TableInterface;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\PermissionsException;

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
     * the main repository as Eloquent Builder
     */
    protected Builder $repository;


    /**
     * __construct
     *
     * @param  string $table
     * @return void
     */
    public function __construct(Request $request)
    {
        // use table as request param if exists
        if ($request->table) {
            $this->use($request->table);
        }
    }

    /**
     * alternative constructor for Facade usage
     *
     * @param  string $table
     * @return self
     */
    public function use(string $table) : self | null
    {
        $this->table = $table;
        $modelClass = $this->convertTableToModel();
        if (class_exists($modelClass)) {
            $this->model = new $modelClass;
            // fill repository
            $this->fillRepository($this->model->where('id','>',0));
        } else {
            throw new TableException("Таблица $table не найдена в описании моделей", 404);
        }
        return $this;
    }

    /**
     * fill repository all table data
     *
     * @param  Builder $builder
     * @return void
     */
    public function fillRepository(Builder $builder)
    {
        $this->repository = $builder;
    }

    /**
     * count filtered data
     *
     * @return void
     */
    public function dataCount() {
        return $this->repository->count();
    }

    /**
     * return class name of resource form model
     *
     * @return void
     */
    public function resourceClass()
    {
        $resourceClass = 'App\\Http\\Resources\\'.Str::singular(Str::studly($this->table)).'Resource';
        return class_exists($resourceClass) ? $resourceClass : null;
    }

    /**
     * return all data from repository
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAll():Collection
    {
        return $this->repository->get();
    }

    /**
     * return array of ids repository rows
     *
     * @return array
     */
    public function getIds():array
    {
        return $this->repository->get()->pluck('id')->all();
    }

    /**
     * convert url table to Laravel Model with namespace
     *
     * @param  mixed $table
     * @return string
     */
    protected function convertTableToModel()
    {
        return 'App\\Models\\'.Str::singular(Str::studly($this->table));
    }

    /**
     * getter Model
     *
     * @param  ?TableInterface $table
     * @return bool
     */
    public function getModel(): ?TableInterface
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
    public function get(Request $request):array
    {
        $this->checks();
        // dd($this->getModel());
        return [
            'data' => $this->repository->filter($request)->limits($request)->get(),
            'count' => $this->dataCount(),
        ];
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
        $row = $this->repository->where('id',$id)->first();
        if ($row) {
            return $row;
        } else {
            throw new TableException("Не удалось извлечь запись с id=$id из таблицы ".$this->model->title()."", 404);
        }
    }

    /**
     * update row identified by $id
     *
     * @param  Illuminate\Http\Reques $request
     * @param  string $table
     * @param  int $id
     * @return ?TableInterface
     */
    public function update(Request $request, string $table, int $id): ?TableInterface
    {
        $this->checks();
        $row = $this->find($table, $id);
        // check permissions
            $rules = $row->validationRules('update');
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
                $validated = $validator->validated();
                // save files
                $files = [];
                if (method_exists($row, 'updateFiles')) {
                    $files = $row->updateFiles($request);
                }
                $dataWithFiles = array_merge($validated, $files);
                // check permissions
                if ($request->user()->can('update', [$row, $dataWithFiles])) {
                    if (!$row->fill($dataWithFiles)->save()) {
                        // clear uploaded files if error
                        if (method_exists($row, 'clearUpload')) {
                            $row->clearUpload($files);
                        }
                    }
                    return $row;
                } else {
                    throw new PermissionsException('Вам запрещено редактировать записи в '.$this->model->title(), 403);
                }
            }

    }

    /**
     * insert new or copy existed row
     *
     * @param  Illuminate\Http\Reques $request
     * @param  string $table
     * @param  int|string|null $id
     * @return ?TableInterface
     */
    public function store(Request $request, string $table, int|string|null $id): ?TableInterface
    {
        $this->checks();
        $rules = $this->model->validationRules('store');
        // replace only requested fields in copied row
        if ($id!=null) {
            $newRules = [];
            foreach($rules as $field=>$rule) {
                if ($request->$field) $newRules[$field] = $rule;
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
            $validatedData = $validator->validated();
            if ($id==null) {
                // new row
                // check permissions
                if ($request->user()->can('create', [$this->model::class, $validatedData])) {
                    // save files
                    $files = [];
                    if (method_exists($this->model, 'storeFiles')) {
                        $files = $this->model->storeFiles($request);
                    }
                    $dataWithFiles = array_merge($validatedData, $files);

                    // save with loaded files
                    if (!$this->model->fill($dataWithFiles)->save()) {
                        // clear uploaded files if error
                        if (method_exists($this->model, 'clearUpload')) {
                            $this->model->clearUpload($files);
                        }
                    }
                    return $this->model;
                } else {
                    throw new PermissionsException('Вам запрещено создавать записи в '.$this->model->title(), 403);
                }
            } else {
                // copy existed row
                $sourceRow = $this->find($table, $id);
                // check permissions
                if ($request->user()->can('copy', [$sourceRow, $validatedData])) {
                    $row = $sourceRow->replicate();
                    $row->fill($validator->validated());
                    $row->save();
                    return $row;
                } else {
                    throw new PermissionsException('Вам запрещено копировать запись в '.$this->model->title(), 403);
                }
            }
        }
        return $this->model;
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
        if ($request->user()->can('delete', $row)) {
            $deleteResult = $row->delete();

            // clear files
            if ($deleteResult) {
                if (method_exists($row, 'deleteFiles')) {
                    $row->deleteFiles();
                }
            }
            return $deleteResult;
        } else {
            throw new PermissionsException('Вам запрещено удалять записи в '.$this->model->title(), 403);
        }
        return false;
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
