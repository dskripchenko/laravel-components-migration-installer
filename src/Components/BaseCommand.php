<?php

namespace Dskripchenko\LaravelCMI\Components;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use \Dskripchenko\LaravelApi\Console\Commands\BaseCommand as BaseApiCommand;

/**
 * Class BaseCommand
 * @package Dskripchenko\LaravelCMI\Components
 */
class BaseCommand extends BaseApiCommand
{
    protected $componentName;


    /**
     * @param string $message
     * @return string
     */
    protected function getNewMigrationName($message = 'Enter migration name'): string
    {
        $name = $this->askValid(
            trans($message),
            [
                'required',
                'min:3',
                'regex:/^[a-zA-Z_]+$/i',
                function ($attribute, $value, $fail) {
                    $className = Str::camel($value);
                    if (class_exists($className)) {
                        $errorMessage = "{$className} " . trans("already exists");
                        $fail($errorMessage);
                    }
                }
            ]
        );
        return Str::camel($name);
    }

    /**
     * @param $file
     * @return false|string
     */
    protected function getMigrationClassNameFromFile($file): string
    {
        if (!is_file($file)) {
            return false;
        }

        $fileContent = file_get_contents($file);
        $pattern = "/^[\s]*?class[\s]*?(?<class>[\S]+?)[\s][\s\S]*?Migration/m";
        preg_match($pattern, $fileContent, $matches);

        return Arr::get($matches, 'class', false);
    }

    /**
     * @param $className
     * @return bool
     */
    protected function isMigrationClassNameExists($className): bool
    {
        $this->preloadMigrationFiles();
        return class_exists($className);
    }


    protected function preloadMigrationFiles(): void
    {
        static $isMigrationLoaded = false;
        if (!$isMigrationLoaded) {
            $isMigrationLoaded = true;
            $dir = database_path('migrations');
            foreach (scandir($dir) as $filename) {
                $filepath = "{$dir}/{$filename}";
                if (is_file($filepath)) {
                    require_once $filepath;
                }
            }
        }
    }
}
