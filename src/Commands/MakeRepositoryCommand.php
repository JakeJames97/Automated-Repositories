<?php

namespace JakeJames\AutomatedRepositories\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use JakeJames\AutomatedRepositories\Traits\MakeRepositoryCommandTrait;
use Symfony\Component\Console\Input\InputArgument;


class MakeRepositoryCommand extends Command
{
    use MakeRepositoryCommandTrait;

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
    protected $type = 'Repository';

    public function handle(): void
    {
        $name = $this->argument('name');
        $name = ucwords(Str::camel($name));
        if (!$this->validateName($name)) {
            $this->error('Invalid name, Please ensure you are using valid characters');
            return;
        }
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
        if ($this->files->exists(
            $path = $this->getPath($this->convertNameForContract($name) . 'ServiceProvider', 'Providers')
        )) {
            $this->error('Service Provider already exists!');
            return false;
        }
        $this->makeDirectory($path);

        try {
            $this->files->put($path, $this->compileProviderStub());
            $providerFile = strstr($path, 'app/');
            $this->registerServiceProvider($providerFile);
        } catch (\Exception $e) {
            $this->error('could not create service provider');
            return false;
        }

        $this->info('Service Provider created successfully.');
        return true;
    }

    /**
     * Registers the service provider in app config
     * @param string $providerPath
     *
     * @return bool
     */
    protected function registerServiceProvider(string $providerPath): bool
    {
        $providerPath = $this->convertToRegisterFormat($providerPath);

        if (!$this->files->isFile(base_path() . '/config/app.php')) {
            $this->error('Your app file could not be found, please register your new provider: ' . $providerPath);
            return false;
        }

        if (!$this->files->isReadable(base_path() . '/config/app.php')) {
            $this->error('Your app file is not readable, please register your new provider: ' . $providerPath);
            return false;
        }

        if (!$this->files->isWritable(base_path() . '/config/app.php')) {
            $this->error('Your app file is not writable, please register your new provider: ' . $providerPath);
            return false;
        }
        try {
            $file_content = $this->files->get(base_path() . '/config/app.php');

            $length = strlen('/*
         * Application Service Providers...
         */');

            $array_start = strpos($file_content, '/*
         * Application Service Providers...
         */');

            $file_content = substr_replace($file_content, $providerPath, $array_start + $length, 0);

            $this->files->put(base_path() . '/config/app.php', $file_content);
        } catch (\Exception $exception) {
            $this->error(
                'We could not register your service provider, please register your new provider: '
                . $providerPath
            );
            return false;
        }

        $this->info('Registered Service Provider');

        return true;
    }

    /**
     * Compile the repository stub.
     *
     * @return string
     * @throws FileNotFoundException
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
     * @throws FileNotFoundException
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
     * @throws FileNotFoundException
     */
    protected function compileProviderStub(): string
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/provider.stub');

        $this->replaceClassName($stub, false, true)->replaceImportNames($stub);

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
     * replaces the import names in the stub
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
                '{{repository_import}}',
                '{{contract_import}}',
                '{{contract_name}}',
                '{{repository_name}}'
            ],
            [
                $className === $contractName ? $className . ' as ' . $contractName . 'Repository' : $className,
                $contractName . ' as ' . $contractName . 'Contract',
                $contractName . 'Contract',
                $contractName . 'Repository'
            ],
            $stub
        );

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
            $stub = str_replace(
                ['{{contract_import}}', '{{contract}}'],
                [$contractName . ' as ' . $contractName . 'Repository', $contractName . 'Repository'],
                $stub
            );
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
