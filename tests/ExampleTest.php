<?php

namespace JakeJames\AutomatedRepositories\Tests;

use Orchestra\Testbench\TestCase;
use JakeJames\AutomatedRepositories\AutomatedRepositoriesServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app): array
    {
        return [AutomatedRepositoresServiceProvider::class];
    }

    /** @test */
    public function true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
