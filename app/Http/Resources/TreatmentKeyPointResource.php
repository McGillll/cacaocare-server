<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentKeyPointResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'disease_id' => $this->disease_id,
            'title' => $this->title,
            'detailed_step' => $this->detailed_step,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
