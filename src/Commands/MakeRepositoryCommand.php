<?php

namespace JakeJames\RepoGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;


class MakeRepositoryCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository with contract';


    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var $composer Composer
     */
    protected $composer;

    /**
     * @var $repoName
     */
    protected $repoName;

    /**
     * MakeRepositoryCommand constructor.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;

        $this->composer = app()['composer'];
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected string $type = 'Repository';

    public function handle(): void
    {
        $name = $this->argument('name');
        $name = ucwords(Str::camel($name));
        $this->repoName = $name;
        $this->createRepository($name);
        $this->createContract($name);
        $this->createServiceProvider($name);

        $this->composer->dumpAutoloads();
    }

    /**
     * creates the repository
     * @param string $name
     *
     * @return bool
     */
    protected function createRepository(string $name): bool
    {
        if ($this->files->exists($path = $this->getPath($name, 'Repositories'))) {
            $this->error('Repository already exists!');
            return false;
        }
        $this->makeDirectory($path);

        try {
            $this->files->put($path, $this->compileRepositoryStub());
        } catch (FileNotFoundException $e) {
            $this->error('could not create repository');
            return false;
        }
        $this->info('Repository created successfully.');
        return true;
    }

    /**
     * creates the contract
     * @param string $name
     *
     * @return bool
     */
    protected function createContract(string $name): bool
    {
        if ($this->files->exists($path = $this->getPath($this->convertNameForContract($name), 'Contracts'))) {
            $this->error('Contract already exists!');
            return false;
        }
        $this->makeDirectory($path);

        try {
            $this->files->put($path, $this->compileContractStub());
        } catch (FileNotFoundException $e) {
            $this->error('Could not create Contract');
            return false;
        }
        $this->info('Contract created successfully.');
        return true;
    }

    /**
     * creates service provider
     * @param string $name
     *
     * @return bool
     */
    protected function createServiceProvider(string $name): bool
    {
        if ($this->files->exists($path = $this->getPath($this->convertNameForContract($name), 'Providers'))) {
            $this->error('Service Provider already exists!');
            return false;
        }
        $this->makeDirectory($path);

        try {
            $this->files->put($path, $this->compileProviderStub());
        } catch (FileNotFoundException $e) {
            $this->error('could not create service provider');
            return false;
        }
        $this->info('Service Provider created successfully.');
        return true;
    }

    /**
     * Compile the repository stub.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function compileRepositoryStub(): string
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/repository.stub');

        $this->replaceClassName($stub, true)->replaceContractName($stub);

        return $stub;
    }

    /**
     * Compile the contract stub.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function compileContractStub(): string
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/contract.stub');

        $this->replaceClassName($stub);

        return $stub;
    }

    /**
     * Compile the provider stub.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function compileProviderStub(): string
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/provider.stub');

        $this->replaceClassName($stub)->replaceImportNames($stub);

        return $stub;
    }

    /**
     * Replace the class name in the stub.
     *
     * @param string $stub
     * @param bool $repository
     * @param bool $provider
     *
     * @return $this
     */
    protected function replaceClassName(&$stub, $repository = false, $provider = false): self
    {
        $className = ucwords(Str::camel($this->argument('name')));

        if (!$repository) {
           $className = $this->convertNameForContract($className);
        }

        if ($provider) {
            $className .= 'ServiceProvider';
        }

        $stub = str_replace('{{class}}', $className, $stub);

        return $this;
    }

    /**
     * @param $stub
     *
     * @return $this
     */
    protected function replaceImportNames(&$stub): self
    {
        $className = ucwords(Str::camel($this->argument('name')));

        $contractName = $this->convertNameForContract($className);

        $stub = str_replace(
            [
                '{{contract_import}}',
                '{{repository_import}}',
                '{{contract_name}}',
                '{{repository_name}}'
            ],
            [
                $contractName . ' as ' . $contractName . 'Repository',
                $contractName . ' as ' . $contractName . 'Contract',
                $contractName . 'Contract',
                $contractName . 'Repository'
            ], $stub);

        return $this;
    }

    /**
     * Replace the contract name in the stub.
     *
     * @param string $stub
     *
     * @return $this
     */
    protected function replaceContractName(&$stub): self
    {
        $className = ucwords(Str::camel($this->argument('name')));

        $contractName = $this->convertNameForContract($className);

        if ($contractName === $this->repoName) {
            $stub = str_replace(['{{contract_import}}', '{{contract}}'],
                [$contractName . ' as ' . $contractName . 'Repository', $contractName . 'Repository'], $stub);
        } else {
            $stub = str_replace(['{{contract_import}}', '{{contract}}'], [$contractName, $contractName], $stub);
        }

        return $this;
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     */
    protected function makeDirectory($path): void
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Get the path to where we should store the migration.
     *
     * @param string $name
     * @param string $type
     *
     * @return string
     */
    protected function getPath(string $name, string $type): string
    {
        return base_path() . '/app/' . $type . '/' . $name . '.php';
    }

    /**
     * Converts the name given for the contract name
     * @param string $name
     *
     * @return string
     */
    protected function convertNameForContract($name): string
    {
        if (strpos($name, 'Repository')) {
            return str_replace('Repository', '', $name);
        }
        if (strpos($name, 'Repo')) {
            return str_replace('Repo', '', $name);
        }
        return $name;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the repository'],
        ];
    }
}
