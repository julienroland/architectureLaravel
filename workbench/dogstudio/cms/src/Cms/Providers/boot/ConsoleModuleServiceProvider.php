<?php namespace Cms\Providers\Boot;

use Cms\Console\Module\Commands\MigrateCommand;
use Cms\Console\Module\Commands\SeedCommand;
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
        foreach ($this->commandsList as $command) {
            $this->{'register' . $command . 'Command'}();
        }
        $this->commands(
            'command.module.migrate',
            'command.module.seed');

    }

    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.module.migrate', function ($app) {

            return new MigrateCommand($app, $app['migrator'], $app['modules']);
        });
    }

    protected function registerSeedCommand()
    {
        $this->app->singleton('command.module.seed', function () {
            return new SeedCommand;
        });
    }

    public function providers()
    {
        $provides = [];
        foreach ($this->commandsList as $command) {
            $provides[] = $this->namespace . $command . 'Command';
        }
        return $provides;
    }

}
