<?php

namespace App\Providers;

use App\Domain\Localization\Services\LocalizationService;
use Illuminate\Support\ServiceProvider;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(LocalizationService::class, function ($app) {
            return new LocalizationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}