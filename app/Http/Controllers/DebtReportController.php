<?php

namespace App\Http\Controllers;

use App\Exceptions\RegisterException;
use App\Http\Resources\DebtKidReportResource;
use App\Http\Resources\DebtReportResource;
use App\Http\Resources\PaymentReportResource;
use App\Http\Resources\PeriodResource;
use App\Http\Resources\PlanResource;
use App\Models\Kid;
use App\Models\Period;
use App\Models\Plan;
use Illuminate\Http\Request;

class DebtReportController extends Controller
{
    private $kids = [];
    private $period = null;

    public function getDebtKidReport(Request $request, int $period_id, int $kid_id ) {

        if (isset($period_id) && isset($kid_id)) {
            $period = Period::find($period_id);
            $kid = Kid::find($kid_id);
            if ($period && $kid) {
                $this->period = $period;

                $plansDebt = [];
                $paymentsDebt = [];

                $plans = $period->plans;
                $publicPlans = $plans->whereNull('kid_id');
                $personalPlans = $plans->where('kid_id', $kid_id);
                $payments = $period->payments->where('kid_id', $kid_id);

                $this->addKid($kid);

                foreach($publicPlans as $plan) {
                    $forKid = $plan->kids()->find($kid_id);
                    if ($forKid) {
                        $plansDebt[] = $plan;
                        $this->addPlan($kid, $plan);
                    }
                }

                foreach($personalPlans as $plan) {
                    $plansDebt[] = $plan;
                    $this->addPlan($kid, $plan);
                }

                foreach($payments as $pay) {
                    $paymentsDebt[] = $pay;
                    $this->addPay($pay);
                }

                return response()->formatApi([
                    'data' => new DebtKidReportResource([
                        'kid' => $this->kids[$kid_id],
                        'plans' => PlanResource::collection($plansDebt),
                        'payments' => PaymentReportResource::collection($paymentsDebt),
                        'period' => new PeriodResource($period),
                    ])
                ]);
            }
        }

        throw new RegisterException('Недостаточно параметров: период, учащийся не указаны или не существуют', 422);
    }
    public function getDebtReport(Request $request, int $period_id)
    {
        if ($period_id) {
            $period = Period::find($period_id);
            if ($period) {
                $this->period = $period;

                // period plans
                $periodPlans = $period->plans;

                $publicPlans = $periodPlans->whereNull('kid_id');
                $personalPlans = $periodPlans->whereNotNull('kid_id');

                foreach($publicPlans as $plan) {
                    $planKids = $plan->kids;
                    foreach ($planKids as $kid) {
                        $this->addPlan($kid, $plan);
                    }
                }

                foreach($personalPlans as $plan) {
                    $this->addPlan($plan->kid, $plan);
                }

                // period payments
                $periodPayments = $period->payments->whereNotNull('kid_id');
                foreach($periodPayments as $payment) {
                    $this->addPay($payment);
                }

                return response()->formatApi([
                    'data' => DebtReportResource::collection($this->kids),
                    'count' => count($this->kids),
                ]);
            }
        }
        throw new RegisterException('Недостаточно параметров: период не указан или не существует', 422);
    }

    private function addKid(Kid $kid) {
        if (!isset($this->kids[$kid->id])) {
            $startSaldo = $this->getStartSaldo($kid);
            $this->kids[$kid->id] = [
                'kid' => $kid,
                'startSaldo' => $startSaldo,
                'sumPlan' => 0,
                'sumPay' => 0,
                'debt' => $startSaldo,
            ];
        }
    }

    private function addPlan(Kid $kid, Plan $plan) {
        $this->addKid($kid);
        $this->kids[$kid->id]['sumPlan'] += $plan->price;
        $this->kids[$kid->id]['debt'] -= $plan->price;
    }

    private function addPay($payment) {
        $kid = $payment->kid;
        $this->addKid($kid);
        $this->kids[$kid->id]['sumPay'] += $payment->amount;
        $this->kids[$kid->id]['debt'] += $payment->amount;
    }

    private function getStartSaldo($kid) {
        $organization = $this->period->organization;
        $prevPeriods = $organization->periods->where('end_date','<',$this->period->start_date);

        $startSaldo = 0;

        foreach($prevPeriods as $period) {
            $periodBalance = $this->getBalance($kid, $period);
            $startSaldo += $periodBalance['debt'];
        }

        return $startSaldo;
    }

    private function getBalance($kid, $period) {
        $balance = [
            'plan' => 0,
            'pay' => 0,
            'debt' => 0,
        ];

        // period plans
        $periodPlans = $period->plans;

        $publicPlans = $periodPlans->whereNull('kid_id');
        $personalPlans = $periodPlans->where('kid_id', '=', $kid->id);

        foreach($publicPlans as $plan) {
            $balance['plan'] += $plan->price;
        }

        foreach($personalPlans as $plan) {
            $balance['plan'] += $plan->price;
        }

        // period payments
        $periodPayments = $period->payments->where('kid_id', '=', $kid->id);
        foreach($periodPayments as $payment) {
            $balance['pay'] += $payment->amount;
        }

        $balance['debt'] = $balance['pay'] - $balance['plan'];

        return $balance;
    }

}
