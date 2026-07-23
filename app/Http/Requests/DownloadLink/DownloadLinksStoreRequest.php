<?php

namespace App\Http\Requests\DownloadLink;

use Illuminate\Foundation\Http\FormRequest;

class DownloadLinksStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'download_link' => 'required|string',
            'version' => 'required|string|unique:download_links,version',
            'description' => 'sometimes|string',
        ];
    }
}
