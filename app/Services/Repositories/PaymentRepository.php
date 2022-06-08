<?php

namespace App\Services\Repositories;

use App\Models\Payment;
use App\Services\TableRepositoryService;
use Illuminate\Http\Request;

class PaymentRepository extends TableRepositoryService
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
        $this->use('payments');
        $this->fillRepository(Payment::whereIn('period_id', $periods->getIds()));
    }


}
