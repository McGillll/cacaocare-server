<?php

namespace App\Http\Requests\DownloadLink;

use Illuminate\Foundation\Http\FormRequest;

class DownloadLinksUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'download_link' => 'sometimes|string',
            'version' => 'sometimes|string|unique:download_links,version,'.$this->route('download_link')?->id,
            'description' => 'sometimes|string',
        ];
    }
}
