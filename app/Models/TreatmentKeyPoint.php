<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentKeyPoint extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentKeyPointFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
        'disease_id',
        'title',
        'detailed_step'
    ];

    public function treatment(){
        return $this->belongsTo(Treatment::class, 'disease_id');
    }
}
