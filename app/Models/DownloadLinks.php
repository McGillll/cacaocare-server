<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLinks extends Model
{
    /** @use HasFactory<\Database\Factories\DownloadLinksFactory> */
    use HasFactory, UsesUuid;

    protected $fillable = [
        'id',
        'uuid',
        'download_link',
        'version',
        'description'
    ];
}
