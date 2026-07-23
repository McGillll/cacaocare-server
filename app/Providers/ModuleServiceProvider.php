<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthServiceInterface;
use App\Interfaces\User\UserServiceInterface;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }
}
