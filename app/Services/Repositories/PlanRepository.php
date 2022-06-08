<?php

namespace App\Services\Repositories;

use App\Models\Plan;
use App\Services\TableRepositoryService;
use Illuminate\Http\Request;

class PlanRepository extends TableRepositoryService
{
    /**
     * current request
     */
    protected Request $request;
    /**
     * use personal constructor with specified fill repository data
     *
     * @param  PeriodRepository $periods
     * @return void
     */
    public function __construct(PeriodRepository $periods) {
        $this->use('plans');
        $this->fillRepository(Plan::whereIn('period_id', $periods->getIds()));
    }


}
