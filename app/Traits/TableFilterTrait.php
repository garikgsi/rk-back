<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Services\TableClasses\TableRequestFilter;

/**
 * Trait add filter scope for model
 *
 */

trait TableFilterTrait
{

    /**
     * scope for filtering base table
     *
     * @param  Builder $builder
     * @param  Request $request
     * @return void
     */
    public function scopeFilter(Builder $builder, Request $request)
    {

        $requestFilter = new TableRequestFilter($request);
        $requestFilter->filter($builder);
    }

}
