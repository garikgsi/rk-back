<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KidParentResource extends JsonResource
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
            'phone' => $this->phone,
            'kid_id' => $this->kid_id,
            'user_id' => $this->user_id,
            'email' => $this->user ? $this->user->email : null,
            'is_admin' => $this->is_admin,
            'organization_id' => $this->kid->organization->id,
        ];
    }
}
