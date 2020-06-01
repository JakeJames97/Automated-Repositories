<?php

namespace JakeJames\AutomatedRepositories\Traits;

trait MakeRepositoryCommandTrait
{
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
     * @param string $name
     *
     * @return false|int
     */
    protected function validateName(string $name)
    {
        return preg_match('/^[a-zA-Z]([a-zA-Z_-])+$/i', $name);
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
     * Formats the string for storing in the config
     * @param $path
     *
     * @return string
     */
    protected function convertToRegisterFormat($path): string
    {
        $path = str_replace(['.php', '/', 'app'], ['::class', '\\', 'App'], $path);

        return  "\n\t\t" . $path .  ',';
    }
}
