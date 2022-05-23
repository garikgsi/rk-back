<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Operation;

class OperationReportResource extends JsonResource
{

    /**
     * Resource using to format collection
     *
     * @var string
     */
    public $collects = Operation::class;


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
            'date_operation'=>$this->date_operation,
            'comment'=>$this->comment,
            'amount'=>$this->amount,
            'check'=>strlen($this->image)>4?$this->image:$this->check_url,
        ];
    }
}
