<?php

namespace App\Http\Requests\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'disease' => 'required|string|unique:treatments,disease',
            'short_description' => 'sometimes|string',
            'img_url' => 'sometimes|url',
            'keypoints' => 'sometimes',
        ];
    }
}
