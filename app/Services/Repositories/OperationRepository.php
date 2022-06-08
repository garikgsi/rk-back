<?php

namespace App\Services\Repositories;

use App\Models\Operation;
use App\Services\TableRepositoryService;
use Illuminate\Http\Request;

class OperationRepository extends TableRepositoryService
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
        $this->use('operations');
        $this->fillRepository(Operation::whereIn('period_id', $periods->getIds()));
    }


}
