<?php

namespace App\Models;

use App\Support\CacaoCache;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Cacao extends Model
{
    /** @use HasFactory<\Database\Factories\CacaoFactory> */
    use HasFactory, HasApiTokens, Notifiable, UsesUuid;

    protected $fillable = [
        "uuid",
        "label",
        "confidence",
        "photo",
        "uploaderId",
        "caption",
        "date_analyzed",
    ];

    protected static function booted()
    {
        static::saved(fn () => CacaoCache::flush());
        static::deleted(fn () => CacaoCache::flush());
    }

    public function user()
    {
        return $this->belongsTo(User::class, "uploaderId");
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
