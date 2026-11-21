<?php

namespace App\Providers;

use App\Auth\ManajerBrokerKataSandi;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Reset tokens are keyed by akun_pengguna.kontak, not the default "email".
        $this->app->extend('auth.password', fn ($manager, $app) => new ManajerBrokerKataSandi($app));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Lists must eager load; a lazy load inside a loop fails loudly outside production.
        Model::preventLazyLoading(! $this->app->isProduction());

        Carbon::setLocale('id');

        $this->aturBatasPermintaan();
    }

    /**
     * Limits for the forms bots like to hammer. Sign-in also counts failed
     * attempts per email in MasukRequest; this caps a single address overall.
     */
    private function aturBatasPermintaan(): void
    {
        RateLimiter::for('masuk', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));

        // "Lupa kata sandi": per IP and per email, like activation requests.
        RateLimiter::for('lupa-sandi', fn (Request $request): array => [
            Limit::perMinutes(15, 5)->by('ip:'.$request->ip()),
            Limit::perHour(3)->by('email:'.Str::lower((string) $request->input('kontak'))),
        ]);

        RateLimiter::for('pendaftaran', fn (Request $request): Limit => Limit::perHour(10)->by($request->ip()));

        // Per email as well, so nobody floods one inbox with activation links.
        RateLimiter::for('aktivasi', fn (Request $request): array => [
            Limit::perMinutes(15, 5)->by('ip:'.$request->ip()),
            Limit::perHour(3)->by('email:'.Str::lower((string) $request->input('kontak'))),
        ]);
    }
}
