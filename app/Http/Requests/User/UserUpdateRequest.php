<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'sometimes|string',
            'password' => 'sometimes|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*_]).+$/',
            'region' => 'sometimes|string',
            'province' => 'sometimes|string',
            'city' => 'sometimes|string',
            'barangay' => 'sometimes|string',
            'profile' => 'sometimes|file|image',
        ];
    }
}
