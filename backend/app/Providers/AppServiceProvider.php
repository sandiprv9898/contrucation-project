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

        // Company Repository Bindings
        $this->app->bind(
            \App\Domain\Companies\Repositories\CompanyProfileRepositoryInterface::class,
            \App\Domain\Companies\Repositories\CompanyProfileRepository::class
        );

        $this->app->bind(
            \App\Domain\Companies\Repositories\CompanyBrandingRepositoryInterface::class,
            \App\Domain\Companies\Repositories\CompanyBrandingRepository::class
        );

        $this->app->bind(
            \App\Domain\Companies\Repositories\CompanyPortfolioRepositoryInterface::class,
            \App\Domain\Companies\Repositories\CompanyPortfolioRepository::class
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
