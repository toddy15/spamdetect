<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

use Illuminate\Support\ServiceProvider;

class SpamDetectServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'spamdetect');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('spamdetect.php'),
        ]);
    }
}
