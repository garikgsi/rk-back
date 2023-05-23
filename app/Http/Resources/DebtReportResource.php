<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DebtReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $kid = $this['kid'];
        return [
            'id' => $kid->id,
            'fio' => $kid->fio,
            'start_study' => $kid->start_study,
            'end_study' => $kid->end_study,
            'is_out' => $kid->is_out,
            'start_saldo' => $this['startSaldo'],
            'sumPlan' => $this['sumPlan'],
            'sumPay' => $this['sumPay'],
            'debt' => $this['debt'],
        ];
    }
}
