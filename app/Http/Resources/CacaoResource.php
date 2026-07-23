<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CacaoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'label' => $this->label,
            'confidence' => $this->confidence,
            'photo' => $this->photo,
            'caption' => $this->caption,
            'date_analyzed' => $this->date_analyzed,
            'uploaderId' => $this->uploaderId,
            'username' => $this->when(isset($this->username), $this->username),
            'profile' => $this->when(isset($this->profile), $this->profile),
            'city' => $this->when(isset($this->city), $this->city),
            'barangay' => $this->when(isset($this->barangay), $this->barangay),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
