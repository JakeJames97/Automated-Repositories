<?php

namespace JakeJames\AutomatedRepositories\Tests\unit\traits;

use Illuminate\Support\Str;
use JakeJames\AutomatedRepositories\Tests\TestCase;
use JakeJames\AutomatedRepositories\Traits\MakeRepositoryCommandTrait;

class MakeRepositoryTraitTest extends TestCase
{
    use MakeRepositoryCommandTrait;

    /**
     * @test
     * @dataProvider pathProvider
     * @param $name
     * @param $type
     */
    public function get_path_returns_correct_path($name, $type): void
    {
        $this->assertEquals(base_path() . '/app/' . $type . '/' . $name . '.php', $this->getPath($name, $type));
    }

    /**
     * @test
     * @dataProvider nameProvider
     * @param string $name
     * @param bool $result
     */
    public function throws_error_with_invalid_names($name, $result): void
    {
        $this->assertEquals($result, $this->validateName($name));
    }

    /**
     * @test
     *
     * @param string $name
     * @param string $base
     *
     * @return void
     *
     * @dataProvider nameForContractProvider
     *
     */
    public function convert_name_for_contract_converts_name_as_expected($name, $base): void
    {
        $name = ucwords(Str::camel($name));
        $name = $this->convertNameForContract($name);
        $this->assertEquals($base, $name);
    }

    /**
     * @dataProvider registerFormatProvider
     * @param $path
     * @param $expected
     */
    public function convert_to_register_format_returns_expected_format($path, $expected): void
    {
        $result = $this->convertToRegisterFormat($path);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function registerFormatProvider(): array
    {
        return [
            ['app/Providers/RegisterServiceProvider.php', 'app\\Providers\\RegisterServiceProvider::class'],
            ['app/Providers/LoginServiceProvider.php', 'app\\Providers\\LoginServiceProvider::class'],
        ];
    }

    /**
     * @return array
     */
    public function nameForContractProvider(): array
    {
        return [
            ['RegisterRepository', 'Register'],
            ['Register_repository', 'Register'],
            ['Register-repository', 'Register'],
            ['LoginRepository', 'Login'],
            ['Login_repository', 'Login'],
            ['Login-repository', 'Login'],
        ];
    }

    /**
     * @return array
     */
    public function nameProvider(): array
    {
        return [
            ['20', false],
            ['@43', false],
            ['389test', false],
            ['~test', false],
            [' ', false],
            ['!', false],
            ['£', false],
            ['$%^&*()', false],
            ['test&', false],
            ['register', true],
            ['registerRepository', true],
            ['register_repository', true],
            ['register-repository', true]
        ];
    }

    /**
     * @return array
     */
    public function pathProvider(): array
    {
        return [
          ['Repositories', 'Register'],
          ['Contracts', 'Login'],
          ['ServiceProvider', 'Login']
        ];
    }
}
