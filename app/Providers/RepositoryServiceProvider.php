<?php

namespace App\Providers;

use App\Interfaces\DownloadLink\DownloadLinkRepositoryInterface;
use App\Interfaces\User\UserRepositoryInterface;
use App\Repositories\DownloadLinkRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DownloadLinkRepositoryInterface::class, DownloadLinkRepository::class);
    }
}
