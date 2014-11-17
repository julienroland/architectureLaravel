<?php namespace Cms\Providers;

use Illuminate\Support\ServiceProvider;

class ConsoleModuleServiceProvider extends ServiceProvider
{
    protected $namespace = 'Cms\\Console\\Module\\Commands\\';
    protected $commandsList = [
//        'Make',
//        'Command',
//        'Controller',
//        'Disable',
//        'Enable',
//        'GenerateFilter',
//        'GenerateProvider',
//        'GenerateRouteProvider',
//        'Install',
//        'List',
        'Migrate',
//        'MigrateRefresh',
//        'MigrateReset',
//        'MigrateRollback',
//        'Migration',
//        'Model',
//        'Publish',
//        'PublishMigration',
        'Seed',

    ];

    public function register()
    {
        foreach ($this->commandsList as $command)
        {
            $this->commands($this->namespace . $command . 'Command');
        }
    }

    public function providers()
    {
        $provides = [];
        foreach ($this->commandsList as $command)
        {
            $provides[] = $this->namespace . $command . 'Command';
        }
        return $provides;
    }
}
