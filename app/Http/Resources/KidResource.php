<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KidResource extends JsonResource
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
            'id' => $this->id,
            'fio' => "$this->last_name $this->name $this->patronymic",
            'last_name' => $this->last_name,
            'name' => $this->name,
            'patronymic' => $this->patronymic,
            'birthday' => $this->birthday,
            'start_study' => $this->start_study,
            'end_study' => $this->end_study,
            'organization_id' => $this->organization_id,
        ];
    }

}
