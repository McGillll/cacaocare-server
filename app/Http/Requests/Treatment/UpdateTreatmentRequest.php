<?php

namespace App\Http\Requests\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'disease' => 'sometimes|string|max:255|unique:treatments,disease,'.$this->route('treatment')?->id,
            'short_description' => 'sometimes|string',
            'img_url' => 'sometimes|url',
            'keypoints' => 'sometimes', // JSON string when sent via FormData
        ];
    }
}
