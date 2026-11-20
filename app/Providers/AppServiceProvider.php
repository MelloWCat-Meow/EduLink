<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Lists must eager load; a lazy load inside a loop fails loudly outside production.
        Model::preventLazyLoading(! $this->app->isProduction());

        Carbon::setLocale('id');
    }
}
