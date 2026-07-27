<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentFactory> */
    use HasFactory, UsesUuid;

    protected $fillable = [
        'id',
        'uuid',
        'disease',
        'short_description',
        'img_url',
    ];

    public function keypoints()
    {
        return $this->hasMany(TreatmentKeyPoint::class, 'disease_id');
    }
}
