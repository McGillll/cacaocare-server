<?php

namespace App\Http\Requests\Cacao;

use Illuminate\Foundation\Http\FormRequest;

class CacaoStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $cacaoData = json_decode($this->input('cacao'), true) ?? [];

        $this->merge($cacaoData);
    }

    public function rules()
    {
        return [
            'label' => 'required|string',
            'confidence' => 'required',
            'date_analyzed' => 'required|date',
            'uploaderId' => 'required|integer|exists:users,id',
            'caption' => 'sometimes|string',
            'photo' => 'required|image|mimes:jpg,jpeg,png',
        ];
    }
}
