<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface;
use App\Domain\Task\Repositories\TaskRepository;
use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use App\Domain\Project\Repositories\ProjectRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
    }

    public function boot(): void
    {
        //
    }
}