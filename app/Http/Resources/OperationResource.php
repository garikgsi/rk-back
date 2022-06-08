<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OperationResource extends JsonResource
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
            'comment'=>$this->comment,
            'date_operation'=> $this->date_operation,
            'price'=>$this->price,
            'quantity'=>$this->quantity,
            'amount'=>$this->amount,
            'image'=>$this->image,
            'plan_id'=>$this->plan_id,
            'period_id'=>$this->period_id,
            'check_url'=>$this->check_url,
        ];
    }
}
