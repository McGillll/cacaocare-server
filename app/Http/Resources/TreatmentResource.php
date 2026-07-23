<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'disease' => $this->disease,
            'short_description' => $this->short_description,
            'img_url' => $this->img_url,
            'keypoints' => TreatmentKeyPointResource::collection($this->whenLoaded('keypoints')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
