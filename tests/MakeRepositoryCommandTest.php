<?php

namespace JakeJames\AutomatedRepositories\Tests;

use Orchestra\Testbench\TestCase;

class MakeRepositoryCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return ['JakeJames\AutomatedRepositories\AutomatedRepositoriesServiceProvider'];
    }

    /** @test */
    public function true_is_true(): void
    {
        $this->artisan('make:repository', ['name' => 'registerRepository'])->assertExitCode(0);
    }
}
