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
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'repo-generator');
        $this->registerRepositoryGenerator();
    }


    /**
     * Register the make:repository generator.
     */
    private function registerRepositoryGenerator(): void
    {
        $this->app->singleton('jakejames.RepoGenerator.Commands.MakeRepositoryCommand', function ($app) {
            return $app['jakejames\RepoGenerator\Commands\MakeRepositoryCommand'];
        });

        $this->commands('jakejames.RepoGenerator.Commands.MakeRepositoryCommand');
    }
}
