<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'title'=> $this->title,
            'price'=>$this->price,
            'quantity'=>$this->quantity,
            'amount'=>$this->amount,
            'period_id'=>$this->period_id,
        ];
    }
}
