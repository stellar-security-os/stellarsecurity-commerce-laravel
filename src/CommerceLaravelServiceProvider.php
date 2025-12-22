<?php

namespace StellarSecurity\CommerceLaravel;

use Illuminate\Support\ServiceProvider;
use StellarSecurity\CommerceLaravel\Client\CommerceClient;
use StellarSecurity\CommerceLaravel\Contracts\CommerceClientContract;

class CommerceLaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/stellarsecurity-commerce-laravel.php', 'stellarsecurity-commerce-laravel');

        $this->app->singleton(CommerceClientContract::class, function () {
            return new CommerceClient(config('stellarsecurity-commerce-laravel'));
        });

        $this->app->alias(CommerceClientContract::class, CommerceClient::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/stellarsecurity-commerce-laravel.php' => config_path('stellarsecurity-commerce-laravel.php'),
        ], 'stellarsecurity-commerce-laravel-config');
    }
}
