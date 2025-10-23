<?php

namespace App\Models;

use App\Http\Controllers\CacaoController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\UsesUuid;
use function PHPUnit\Framework\returnArgument;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, UsesUuid;

    protected $fillable = [
        'id',
        'uuid',
        'username',
        'email',
        'password',
        'profile',
        'region',
        'province',
        'city',
        'barangay',
        'role',
        'email_verified_at'
    ];

    public function cacaos(){
        return $this->hasMany(Cacao::class, 'uploaderId');
    }
}
