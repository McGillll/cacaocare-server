<?php

namespace App\Http\Requests\Cacao;

use Illuminate\Foundation\Http\FormRequest;

class CacaoUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'label' => 'sometimes|string',
            'caption' => 'sometimes|string',
            'date_analyzed' => 'sometimes|date',
        ];
    }
}
