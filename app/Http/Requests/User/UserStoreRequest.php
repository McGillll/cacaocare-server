<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|string|unique:users,username',
            'email' => 'required|unique:users,email|email',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*_]).+$/',
            'barangay' => 'required|string',
            'region' => 'sometimes|string',
            'province' => 'sometimes|string',
            'city' => 'sometimes|string',
        ];
    }
}
