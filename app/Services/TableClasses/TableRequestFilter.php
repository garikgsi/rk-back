<?php

namespace App\Services\TableClasses;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Facades\Table;
use App\Interfaces\TableInterface;
use Carbon\Carbon;
use PhpParser\Node\Stmt\TryCatch;

class TableRequestFilter
{
    protected Request $request;
    protected Builder $builder;
    protected TableInterface $model;

    /**
     * __construct
     *
     * @param  Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->model = Table::use($request->table)->getModel();
    }

    /**
     * filtering builder using a request filter values
     *
     * @param  Builder $builder
     * @return void
     */
    public function filter(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->parseFilters() as $filter) {
            // field
            $fieldName = $filter['field']->getName();
            $fieldType = $filter['field']->getType();
            $fieldFilter = trim($filter['values']);
            $expression = $filter['expression'];
            // operands
            if (!isset($nextOperand)) {
                $operand = 'and';
            } else {
                $operand = $nextOperand;
            }
            $nextOperand = $filter['operand'];
            // builder request, like where or orWhere
            switch ($operand) {
                case 'or': {
                    $builderRequest = 'orWhere';
                } break;
                case 'and': default: {
                    $builderRequest = 'where';
                }
            }
            // make a builder request
            $this->builder->$builderRequest(function(Builder $query) use ($fieldName, $fieldType, $fieldFilter, $expression){
                // different requests
                switch ($expression) {
                    case 'like': {
                        $searchWords = explode(' ', str_replace(['"',"'"],'', $fieldFilter));
                        foreach($searchWords as $searchWord) {
                            $query->where($fieldName, 'like', "%$searchWord%");
                        }
                    } break;
                    case 'in': case 'ni': {
                        // json decode string
                        $searchArray = json_decode($fieldFilter, true);
                        if ($searchArray===null || !is_array($searchArray)) {
                            $searchArray = [$fieldFilter];
                        }
                        // cast to $fieldType and validate array values
                        $validatedArray = [];
                        switch ($fieldType) {
                            case 'boolean': {
                                foreach ($searchArray as $searchValue) {
                                    $validatedFilter = filter_var($searchValue, FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);
                                    if ($validatedFilter !== null) {
                                        $validatedArray[] = $validatedFilter?1:0;
                                    }
                                }
                            } break;
                            case 'number': case 'select': {
                                foreach ($searchArray as $searchValue) {
                                    $validatedFilter = filter_var($searchValue, FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);
                                    if ($validatedFilter !== null) {
                                        $validatedArray[] = $validatedFilter;
                                    }
                                }
                            } break;
                            case 'money': {
                                foreach ($searchArray as $searchValue) {
                                    $validatedFilter = filter_var($searchValue, FILTER_VALIDATE_FLOAT,FILTER_NULL_ON_FAILURE);
                                    if ($validatedFilter !== null) {
                                        $validatedArray[] = $validatedFilter;
                                    }
                                }
                            } break;
                            // default: {
                            //     $validatedArray[] = $searchArray;
                            // }
                        }
                        // make request if only exists validated values
                        if (count($validatedArray)>0) $query->{$expression=='in'?'whereIn':'whereNotIn'}($fieldName, $validatedArray);
                    } break;
                    case 'ne': case 'eq': case 'gt': case 'gte': case 'lt': case 'lte': {
                        switch ($expression) {
                            case 'eq': {
                                $expr = '=';
                            } break;
                            case 'ne': {
                                $expr = '<>';
                            } break;
                            case 'gt': {
                                $expr = '>';
                            } break;
                            case 'gte': {
                                $expr = '>=';
                            } break;
                            case 'lt': {
                                $expr = '<';
                            } break;
                            case 'lte': {
                                $expr = '<=';
                            } break;
                        }
                        // cast to $fieldType, validate and make builder request if filter value is valid
                        switch ($fieldType) {
                            case 'boolean': {
                                $validatedFilter = filter_var($fieldFilter, FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);
                                if ($validatedFilter !== null) {
                                    $query->where($fieldName, $expr, $validatedFilter?1:0);
                                }
                            } break;
                            case 'number': {
                                $validatedFilter = filter_var($fieldFilter, FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);
                                if ($validatedFilter !== null) {
                                    $query->where($fieldName, $expr, $validatedFilter);
                                }
                            } break;
                            case 'money': {
                                $validatedFilter = filter_var($fieldFilter, FILTER_VALIDATE_FLOAT,FILTER_NULL_ON_FAILURE);
                                if ($validatedFilter !== null) {
                                    $query->where($fieldName, $expr, $validatedFilter);
                                }
                            } break;
                            case 'datetime':case 'date': {
                                try {
                                    $validDate = new Carbon($fieldFilter);
                                    $validDate = $validDate->format('Y-m-d');
                                    $query->whereDate($fieldName, $expr, $validDate);
                                } catch (\Throwable $th) {
                                    // nothing to filter
                                }
                            } break;
                            default: {
                                $query->where($fieldName, $expr, $fieldFilter);
                            }
                        }
                    } break;
                }
            });
        }
        // $this->builder->dd();
    }

    /**
     * parse filters from request
     *
     * @return array
     */
    public function parseFilters():array
    {
        $res = [];
        if ($this->request->has('filter')) {
            $filtersString = $this->request->filter;
            preg_match_all(
                '/(([\w\.]+)\s{1,}(eq|ne|like|in|ni|gt|gte|lt|lte)\s{1,}([\d\(\)\s\-\+]*|\"{0,1}\d{4}-\d{2}-\d{2}(\s{1,}\d{1,2}:\d{1,2}:\d{1,2}\s{0,}){0,1}\"{0,1}|[\"\']([\wа-я]+\s?)+[\"\']|[\wа-я]+|\d+\.{0,1}\d{0,}|\[(\s{0,}(\"{0,1}[\wа-я\.]+\"{0,1})\s{0,}\,{0,}\s{0,}){1,}\])((\s{1,}(and|or))|\s{0,}$)){0,}/mui',
                $filtersString,
                $matchesFilters
            );
            $filterNames = $matchesFilters[2];
            $filterExpressions = $matchesFilters[3];
            $filtersValues = $matchesFilters[4];
            $filtersOperands = $matchesFilters[11];

            foreach($filterNames as $key=>$field) {
                $field = $this->model->getField($field);
                if ($field) {
                    $res[] = [
                        "field" => $field,
                        "expression"=> $filterExpressions[$key],
                        "values" => $filtersValues[$key],
                        "operand" => $filtersOperands[$key]?:'and'
                    ];
                }
            }
        }
        // dd($res);
        return $res;
    }

}
