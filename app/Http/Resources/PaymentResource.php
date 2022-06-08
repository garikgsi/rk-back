<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'date_payment'=> $this->date_payment,
            'comment'=>$this->comment,
            'amount'=>$this->amount,
            'kid_id'=>$this->kid_id,
            'period_id'=>$this->period_id,
        ];
    }
}
