<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Services\TableClasses\TableRequestOrderLimits;


trait TableOrderLimitsTrait
{
    /**
     * scopeLimits
     *
     * @param  Builder $builder
     * @param  Request $request
     * @return void
     */
    public function scopeLimits(Builder $builder, Request $request)
    {
        // dd($this);
        $requestLimits = new TableRequestOrderLimits($request, $this);
        $requestLimits->setOrderLimits($builder);

    }


}
