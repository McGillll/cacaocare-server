<?php

namespace App\Providers;

use App\Interfaces\Cacao\CacaoRepositoryInterface;
use App\Interfaces\Comment\CommentRepositoryInterface;
use App\Interfaces\DownloadLink\DownloadLinkRepositoryInterface;
use App\Interfaces\Treatment\TreatmentRepositoryInterface;
use App\Interfaces\User\UserRepositoryInterface;
use App\Repositories\CacaoRepository;
use App\Repositories\CommentRepository;
use App\Repositories\DownloadLinkRepository;
use App\Repositories\TreatmentRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DownloadLinkRepositoryInterface::class, DownloadLinkRepository::class);
        $this->app->bind(TreatmentRepositoryInterface::class, TreatmentRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
        $this->app->bind(CacaoRepositoryInterface::class, CacaoRepository::class);
    }
}
