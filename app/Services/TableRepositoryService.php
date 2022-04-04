<?php

namespace App\Services;

use App\Exceptions\TableException;
use App\Interfaces\TableInterface;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
     */
    public function find(string $table, int $id)
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
     * check if repository is initialized and classes implemented such interfaces
     *
     * @return void
     */
    protected function checks()
    {
        if ($this->model==null || $this->table=='') {
            throw new TableException("Репозиторий не идентифицирован", 421);
        }
    }
}
