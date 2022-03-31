<?php

namespace App\Services;

use App\Exceptions\TableException;
use App\Interfaces\TableInterface;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/**
 * Main Service repository for manipulate data
 */
class TableRepositoryService {

    /**
     * Model implemented custom TableInterface
     *
     */
    protected TableInterface $model;

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
        return $this->model->get()->take($request->limit)->toArray();
    }


}
