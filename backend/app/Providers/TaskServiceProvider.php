<?php

namespace App\Providers;

use App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface;
use App\Domain\Task\Repositories\TaskRepository;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repository interface binding
        $this->app->bind(
            TaskRepositoryInterface::class,
            TaskRepository::class
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