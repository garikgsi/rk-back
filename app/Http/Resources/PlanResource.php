<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            'created_at'=>$this->created_at->format('Y-m-d'),
            'start_bill_date' => $this->start_bill_date,
            'kid_id' => $this->kid_id,
            'kid_fio'=>$this->kid_fio,
        ];
    }
}
