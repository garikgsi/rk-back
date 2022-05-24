<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Period;
use App\Http\Resources\OperationReportResource;
use App\Http\Resources\PeriodReportResource;
use App\Http\Resources\PlanReportResource;
use App\Models\Payment;
use App\Models\Plan;
use App\Exceptions\TableException;

class PublicReportController extends Controller
{
    /**
     * return public data
     *
     * @param  mixed $request
     * @return void
     */
    public function __invoke(Request $request)
    {
        // period
        $periods = Period::get();
        try {
            $period = Period::findOrFail($request->period_id);
        } catch (\Throwable $th) {
            $period = $periods->last();
        }

        if ($period) {
            // operations
            $operations = $period->operations()->get();
            $sumOperations = $operations->sum('amount');

            // plans
            $plans = $period->plans()->get();
            $sumPlans = $plans->sum('amount');

            // payments
            $sumPayments = $period->payments()->whereNotNull('kid_id')->get()->sum('amount');

            // debt for start period
            $earlyPeriods = Period::where('id','<',$period->id)->pluck('id')->values();
            $startDebt = Payment::whereIn('period_id',$earlyPeriods)->whereNotNull('kid_id')->get()->sum('amount') - Plan::whereIn('period_id',$earlyPeriods)->get()->sum('amount');
            $earlyCashback = Payment::whereIn('period_id',$earlyPeriods)->whereNull('kid_id')->get()->sum('amount');
            $currentCashback = $period->payments()->whereNull('kid_id')->get()->sum('amount');
            $startSaldo = $startDebt + $earlyCashback + $currentCashback;
            // return report data
            return response()->formatApi([
                'data' => [
                    'current_period' => new PeriodReportResource($period),
                    'operations' => OperationReportResource::collection($operations),
                    'plans'=> PlanReportResource::collection($plans),
                    'periods' => PeriodReportResource::collection($periods),
                    'totals' => [
                        'operations' => round($sumOperations,2),
                        'plans' => round($sumPlans,2),
                        'payments' => round($sumPayments,2),
                        'startDebt' => round($startDebt,2),
                        'startSaldo' => round($startSaldo,2)
                    ],
                ]
            ]);
        } else {
            throw new TableException('Похоже, данные еще не были внесены', 422);
        }
    }
}
