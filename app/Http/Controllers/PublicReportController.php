<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OperationReportResource;
use App\Http\Resources\PeriodReportResource;
use App\Http\Resources\PlanReportResource;
use App\Http\Resources\OrganizationReportResource;
use App\Models\Organization;
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
        $organization = Organization::where('slug',$request->slug)->first();
        if ($organization) {

            // period
            $organizationPeriods = $organization->periods;
            if ($request->period_id) {
                $period = $organizationPeriods->find($request->period_id);
            } else {
                $period = $organizationPeriods->sortBy([['end_date','desc'],['id','desc']])->values()->first();
            }
            if ($period) {
                // organization data
                $organizationPayments = $organization->payments;
                $oragnizationPlans = $organization->plans;

                // period operations
                $operations = $period->operations()->get();
                $sumOperations = $operations->sum('amount');
                $oragnizationOperations = $organization->operations;

                // period plans
                $periodPlans = $period->plans;
                $sumPlans = $periodPlans->sum('amount');

                // period payments
                $periodPayments = $period->payments;
                $sumPayments = $periodPayments->whereNotNull('kid_id')->sum('amount');

                // debt for start period
                $earlyPeriods = $organizationPeriods->where('id','<',$period->id)->pluck('id')->values();
                $earlyOperations = $oragnizationOperations->whereIn('period_id',$earlyPeriods)->sum('amount');
                $earlyPayments = $organizationPayments->whereIn('period_id',$earlyPeriods)->whereNotNull('kid_id')->sum('amount');
                $earlyPlans = $oragnizationPlans->whereIn('period_id',$earlyPeriods)->sum('amount');
                $startDebt = $earlyPayments - $earlyPlans;
                // $startDebt = $organizationPayments->whereIn('period_id',$earlyPeriods)->whereNotNull('kid_id')->sum('amount') - $oragnizationPlans->whereIn('period_id',$earlyPeriods)->sum('amount');
                $earlyCashback = $organizationPayments->whereIn('period_id',$earlyPeriods)->whereNull('kid_id')->sum('amount');
                $currentCashback = $periodPayments->whereNull('kid_id')->sum('amount');
                $currentPayments = $organizationPayments->whereIn('period_id',$period->id)->whereNotNull('kid_id')->sum('amount');
                $currentOperations = $oragnizationOperations->whereIn('period_id',$period->id)->sum('amount');

                $startSaldo = $earlyPayments - $earlyOperations + $earlyCashback;
                $currentSaldo = $startSaldo + $currentCashback + $currentPayments - $currentOperations;
                // $startSaldo = $startDebt + $earlyCashback + $currentCashback - $earlyOperations;
                // return report data
                return response()->formatApi([
                    'data' => [
                        'organization' => new OrganizationReportResource($organization),
                        'current_period' => new PeriodReportResource($period),
                        'operations' => OperationReportResource::collection($operations),
                        'plans'=> PlanReportResource::collection($periodPlans),
                        'periods' => PeriodReportResource::collection($organizationPeriods),
                        'totals' => [
                            'operations' => round($sumOperations,2),
                            'plans' => round($sumPlans,2),
                            'payments' => round($sumPayments,2),
                            'startDebt' => round($startDebt,2),
                            'startSaldo' => round($startSaldo,2),
                            'currentSaldo' => round($currentSaldo,2)
                        ],
                    ]
                ]);
            } else {
                throw new TableException('Похоже, данные еще не были внесены', 422);
            }
        } else {
            throw new TableException('Невозможно прочитать отчет', 404);
        }

    }
}
