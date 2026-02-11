<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AudiusService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AudiusService::class);    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
