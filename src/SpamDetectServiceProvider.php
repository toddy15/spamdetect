<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

use Illuminate\Support\ServiceProvider;

class SpamDetectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'spamdetect');
        $this->app->bind('spamdetect', function ($app) {
            return new SpamDetect();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('spamdetect.php'),
        ]);
    }
}
