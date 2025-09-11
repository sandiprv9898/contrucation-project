<?php

namespace App\Providers;

use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use App\Domain\Project\Repositories\ProjectRepository;
use Illuminate\Support\ServiceProvider;

class ProjectServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repository interface binding
        $this->app->bind(
            ProjectRepositoryInterface::class,
            ProjectRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}