<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DebtKidReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $kidWithDigits = $this['kid'];
        $kid = $kidWithDigits['kid'];
        return [
            'id' => $kid->id,
            'fio' => $kid->fio,
            'start_study' => $kid->start_study,
            'end_study' => $kid->end_study,
            'is_out' => $kid->is_out,
            'start_saldo' => $kidWithDigits['startSaldo'],
            'sumPlan' => $kidWithDigits['sumPlan'],
            'sumPay' => $kidWithDigits['sumPay'],
            'debt' => $kidWithDigits['debt'],
            'plans' => $this['plans'],
            'payments' => $this['payments'],
            'period' => $this['period']
        ];
    }
}
