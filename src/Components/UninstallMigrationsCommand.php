<?php


namespace Dskripchenko\LaravelCMI\Components;


use Illuminate\Support\Facades\Artisan;

class UninstallMigrationsCommand extends BaseCommand
{
    protected $signature = 'cmi:component:uninstall';

    protected $description = 'Removing component migrations';

    public function handle()
    {
        $this->output->warning($this->getMessage());

        $message = trans("Create component uninstall migration") . " {$this->componentName}?";
        if ($this->confirm($message, false)) {
            $name = $this->getNewMigrationName();
            Artisan::call("make:migration", ['name' => $name]);
        }
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        $message = <<<RAW_MESSAGE
In order to ensure data integrity
component tables will NOT be deleted automatically.
Create safe drop table migrations manually.
RAW_MESSAGE;

        return trans($message);
    }
}
