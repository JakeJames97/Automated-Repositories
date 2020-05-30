<?php

namespace JakeJames\RepoGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InvalidArgumentException;
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
        $this->validateName($name);
        $this->createRepository($name);
        $this->createContract($name);

        $this->composer->dumpAutoloads();
    }

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

    protected function createContract(string $name): bool
    {
        if ($this->files->exists($path = $this->getPath($name, 'Contracts'))) {
            $this->error('Contract already exists!');
            return false;
        }
        $this->makeDirectory($path);

        try {
            $this->files->put($path, $this->compileContractStub());
        } catch (FileNotFoundException $e) {
            $this->error('could not create contract');
            return false;
        }
        $this->info('Contract created successfully.');
        return true;
    }

    protected function validateName($name): void
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $name)) {
            throw new InvalidArgumentException('Repository name contains invalid characters.');
        }
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
     * Replace the class name in the stub.
     *
     * @param string $stub
     * @param bool $repository
     *
     * @return $this
     */
    protected function replaceClassName(&$stub, $repository = false): self
    {
        $className = ucwords(Str::camel($this->argument('name')));

        if (!$repository) {
            str_replace('Repository', '', $className);
        }

        $stub = str_replace('{{class}}', $className, $stub);

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

        $contractName = str_replace('Repository', '', $className);

        $stub = str_replace('{{contract}}', $contractName, $stub);

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
        return base_path() . '/' . $type . '/' . $name . '.php';
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
