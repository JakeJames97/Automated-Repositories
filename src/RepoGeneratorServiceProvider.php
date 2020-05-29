<?php

namespace JakeJames\RepoGenerator;

use Illuminate\Support\ServiceProvider;

class RepoGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('repo-generator.php'),
            ], 'config');

             $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'repo-generator');
    }
}
