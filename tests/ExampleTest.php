<?php

namespace JakeJames\RepoGenerator\Tests;

use Orchestra\Testbench\TestCase;
use JakeJames\RepoGenerator\RepoGeneratorServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [RepoGeneratorServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
