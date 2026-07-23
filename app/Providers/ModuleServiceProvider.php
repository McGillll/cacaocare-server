<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthServiceInterface;
use App\Interfaces\Cacao\CacaoServiceInterface;
use App\Interfaces\Comment\CommentServiceInterface;
use App\Interfaces\DownloadLink\DownloadLinkServiceInterface;
use App\Interfaces\Treatment\TreatmentServiceInterface;
use App\Interfaces\User\UserServiceInterface;
use App\Services\Auth\AuthService;
use App\Services\Cacao\CacaoService;
use App\Services\Comment\CommentService;
use App\Services\DownloadLink\DownloadLinkService;
use App\Services\Treatment\TreatmentService;
use App\Services\User\UserService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DownloadLinkServiceInterface::class, DownloadLinkService::class);
        $this->app->bind(TreatmentServiceInterface::class, TreatmentService::class);
        $this->app->bind(CommentServiceInterface::class, CommentService::class);
        $this->app->bind(CacaoServiceInterface::class, CacaoService::class);
    }
}
