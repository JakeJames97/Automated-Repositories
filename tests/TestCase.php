<?php

namespace JakeJames\AutomatedRepositories\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = app()->make(Filesystem::class);

        config()->set(['automatedRepositories.directory.repositories' => 'App/Repositories']);
        config()->set(['automatedRepositories.directory.contracts' => 'App/Contracts']);
        config()->set(['automatedRepositories.directory.providers' => 'App/Providers']);
    }

    protected function removeAddedFiles($name): void
    {
        $base_name = ucwords(Str::camel($name));
        if (strpos($base_name, 'Repository')) {
            $base_name = str_replace('Repository', '', $base_name);
        }
        if (strpos($base_name, 'Repo')) {
            $base_name = str_replace('Repo', '', $base_name);
        }
        // remove register service provider
        $file = $this->files->get(base_path() . '/config/app.php');
        $updated_file = str_replace('App\Providers\\' . $base_name . 'ServiceProvider::class,', '', $file);
        $this->files->put(base_path() . '/config/app.php', $updated_file);

        if (is_file($file = base_path() . '/app/Contracts/' . $base_name  . '.php')) {
            $this->files->delete($file);
        }
        if (is_file($file = base_path() . '/app/Repositories/' . $base_name . 'Repository.php')) {
            $this->files->delete($file);
        }
        if (is_file($file = base_path() . '/app/Providers/' . $base_name . 'ServiceProvider.php')) {
            $this->files->delete($file);
        }
    }
}
