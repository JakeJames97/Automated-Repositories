<?php

namespace JakeJames\AutomatedRepositories;

use Illuminate\Support\ServiceProvider;

class AutomatedRepositoriesServiceProvider extends ServiceProvider
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
        $this->app->singleton('jakejames.AutomatedRepositories.Commands.MakeRepositoryCommand', function ($app) {
            return $app['JakeJames\AutomatedRepositories\Commands\MakeRepositoryCommand'];
        });

        $this->commands('jakejames.AutomatedRepositories.Commands.MakeRepositoryCommand');
    }
}
