<?php

namespace App\Services\TableClasses;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Facades\Table;
use App\Services\TableClasses\TableModel;

class TableRequestOrderLimits
{
    protected Request $request;
    protected Builder $builder;
    protected TableModel $model;
    protected int $offset = 0;
    protected int $page = 1;
    protected int $rowsPerPage = 10;
    protected array $orders = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * apply sorting and limits
     *
     * @param  Builder $builder
     * @return void
     */
    public function setOrderLimits(Builder $builder) {
        $this->builder = $builder;
        $this->order();
        $this->limits();
        // $this->builder->dd();
    }

    /**
     * set order request
     *
     * @return void
     */
    protected function order() {
        $this->model = Table::use($this->request->table)->getModel()->getFields();
        if ($this->request->has('sort')) {
            // we would use multiple sorting
            // separate sortField and sortOrder by dot(.)
            // you don't need use dot to sort ASC
            // example ?sort=name.asc,created_at.desc,is_active
        }
        preg_match_all(
            '/(\w+)(\.(\w+)){0,}/mi',
            $this->request->sort,
            $requestOrder
        );
        // add sort to orders
        $sortFields = $requestOrder[1];
        $sortOrders = $requestOrder[3];
        foreach($sortFields as $index=>$sortField) {
            if ($this->model->has($sortField)) {
                $this->orders[] = [
                    $sortField,
                    isset($sortOrders[$index])&&strtolower($sortOrders[$index])=='desc'?'desc':'asc'
                ];
            }
        }
        // apply sorting or add default order by id asc
        if (count($this->orders)>0) {
            foreach($this->orders as $order) {
                $this->builder->orderBy($order[0], isset($order[1])?$order[1]:'asc');
            }
        } else {
            $this->builder->orderBy('id','asc');
        }
    }


    /**
     * set limits request
     *
     * @return void
     */
    protected function limits() {
        $noLimits = false;
        // check limit in request
        if ($this->request->has('limit')) {
            $this->rowsPerPage = intVal($this->request->limit);
            if ($this->rowsPerPage == 0) $noLimits = true;
        }
        // check offset in request
        if ($this->request->has('offset')) {
            $this->offset = intVal($this->request->offset);
        }
        // check page in request
        if ($this->request->has('page')) {
            $this->page = intVal($this->request->page);
        }
        // check rows per page in request
        if ($this->request->has('rows')) {
            $this->rowsPerPage = intVal($this->request->rows);
        }

        if (!$noLimits) {
            $this->builder->skip($this->calcOffset())->take($this->rowsPerPage);
        }
    }

    /**
     * calcutate offset using offset, page & rowsPerPage
     *
     * @return int
     */
    protected function calcOffset():int {
        return $this->offset + $this->rowsPerPage * ($this->page-1);
    }
}
