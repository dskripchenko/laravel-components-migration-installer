<?php

namespace Dskripchenko\LaravelCMI\Components;

use Illuminate\Support\Facades\Artisan;

/**
 * Class InstallMigrationsCommand
 * @package Dskripchenko\LaravelCMI\Components
 */
abstract class InstallMigrationsCommand extends BaseCommand
{
    protected $signature = 'cmi:component:install';

    protected $description = "Installing component migrations";

    public function handle(): void
    {
        $this->installMigrations();
    }

    protected function installMigrations(): void
    {
        $message = trans("Install component migrations") . " {$this->componentName}?";
        if ($this->confirm($message, false)) {
            $targetDir = database_path('migrations');
            $this->copyMigrationsToPath($targetDir);
            if ($this->confirm(trans("Apply migrations?"), false)) {
                Artisan::call("migrate");
            }
        }
    }

    /**
     * @param string $path
     */
    protected function copyMigrationsToPath(string $path): void
    {
        $migrations = $this->getMigrationFilePathMap($path);
        if (!is_dir($path)) {
            if (!mkdir($path) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        foreach ($migrations as $originFile => $targetFile) {
            $className = $this->getMigrationClassNameFromFile($originFile);
            if ($this->isMigrationClassNameExists($className)) {
                $message = "{$className} from migration {$originFile} already exist, continue copying this migration?";
                if (!$this->confirm($message, false)) {
                    continue;
                }
            }

            copy($originFile, $targetFile);
        }
    }

    /**
     * @param string $targetDir
     * @return array
     */
    protected function getMigrationFilePathMap(string $targetDir): array
    {
        $dir = $this->getMigrationsDir();
        $timestamp = date('Y_m_d_His');
        $map = [];
        foreach ($this->getMigrations() as $migration) {
            $path = $migration;
            if (!is_file($path)) {
                $path = trim($path, '/');
                $path = "{$dir}/{$path}";
            }
            $fileName = basename($path);
            $map[$path] = "{$targetDir}/{$timestamp}-{$fileName}";
        }
        return $map;
    }

    /**
     * @return array of files
     */
    abstract protected function getMigrations(): array;

    /**
     * @return string
     */
    abstract protected function getMigrationsDir(): string;
}
