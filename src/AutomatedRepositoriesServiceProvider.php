<?php

namespace JakeJames\AutomatedRepositories;

use Illuminate\Support\ServiceProvider;
use JakeJames\AutomatedRepositories\Commands\MakeRepositoryCommand;

class AutomatedRepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/automatedRepositories.php' => config_path('automated-repositories.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/automatedRepositories.php', 'automated-repositories');
        $this->registerRepositoryGenerator();
    }


    /**
     * Register the make:repository generator.
     */
    private function registerRepositoryGenerator(): void
    {
        $this->app->singleton('jakejames.AutomatedRepositories.Commands.MakeRepositoryCommand', function ($app) {
            return $app[MakeRepositoryCommand::class];
        });

        $this->commands('jakejames.AutomatedRepositories.Commands.MakeRepositoryCommand');
    }
}
