<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Settings Repository Binding
        $this->app->bind(
            \App\Domain\Settings\Repositories\SettingRepositoryInterface::class,
            \App\Domain\Settings\Repositories\SettingRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
