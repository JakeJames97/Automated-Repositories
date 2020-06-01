<?php

namespace JakeJames\AutomatedRepositories\Tests\feature\commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

class MakeRepositoryCommandTest extends TestCase
{
    /**
     * @var Filesystem $files
     */
    protected $files;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = app()->make(Filesystem::class);
    }

    protected function getPackageProviders($app): array
    {
        return ['JakeJames\AutomatedRepositories\AutomatedRepositoriesServiceProvider'];
    }

    /**
     * @test
     * @dataProvider fileNameProvider
     * @param $name
     * @param $base_name
     */
    public function make_repository_command_creates_all_expected_files($name, $base_name): void
    {
        $this->artisan('make:repository', ['name' => $name])
            ->expectsOutput('Repository created successfully.')
            ->expectsOutput('Contract created successfully.')
            ->expectsOutput('Registered Service Provider')
            ->expectsOutput('Service Provider created successfully.')
            ->assertExitCode(0);

        $this->assertFileExists(base_path() . '/app/Contracts/' . $base_name . '.php');
        $this->assertFileExists(base_path() . '/app/Repositories/' . $base_name . 'Repository.php');
        $this->assertFileExists(base_path() . '/app/Providers/' . $base_name .  'ServiceProvider.php');

        $this->removeAddedFiles($name);
    }

    /**
     * @test
     */
    public function running_make_repository_command_twice_throws_messages_saying_already_exists(): void
    {
        $base_name = 'Register';

        $this->artisan('make:repository', ['name' => 'RegisterRepository'])
            ->expectsOutput('Repository created successfully.')
            ->expectsOutput('Contract created successfully.')
            ->expectsOutput('Registered Service Provider')
            ->expectsOutput('Service Provider created successfully.')
            ->assertExitCode(0);

        $this->assertFileExists(base_path() . '/app/Contracts/' . $base_name . '.php');
        $this->assertFileExists(base_path() . '/app/Repositories/' . $base_name . 'Repository.php');
        $this->assertFileExists(base_path() . '/app/Providers/' . $base_name .  'ServiceProvider.php');

        $this->artisan('make:repository', ['name' => 'RegisterRepository'])
            ->expectsOutput('Repository already exists!')
            ->expectsOutput('Contract already exists!')
            ->expectsOutput('Service Provider already exists!')
            ->assertExitCode(0);

        $this->assertFileExists(base_path() . '/app/Contracts/' . $base_name . '.php');
        $this->assertFileExists(base_path() . '/app/Repositories/' . $base_name . 'Repository.php');
        $this->assertFileExists(base_path() . '/app/Providers/' . $base_name .  'ServiceProvider.php');

        $this->removeAddedFiles('RegisterRepository');
    }

    /**
     * @test
     * @dataProvider incorrectNameProvider
     * @param string $name
     */
    public function throws_error_with_invalid_names($name): void
    {
        $this->artisan('make:repository', ['name' => $name])
            ->expectsOutput('Invalid name, Please ensure you are using valid characters')
            ->assertExitCode(0);
    }

    /**
     * @return array
     */
    public function incorrectNameProvider(): array
    {
        return [
            ['20'],
            ['@43'],
            ['389test'],
            ['~test'],
            [' '],
            ['!'],
            ['£'],
            ['$%^&*()'],
            ['test&']
        ];
    }

    /**
     * @return array
     */
    public function fileNameProvider(): array
    {
        return [
            ['RegisterRepository', 'Register'],
            ['LoginRepository', 'Login'],
            ['login_repository', 'Login'],
            ['login-repository', 'Login'],
        ];
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
