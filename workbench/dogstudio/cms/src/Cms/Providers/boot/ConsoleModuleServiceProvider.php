<?php namespace Cms\Providers\Boot;

use Cms\Console\Module\Commands\MigrateCommand;
use Cms\Console\Module\Commands\PublishAssetCommand;
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
        'PublishAsset',
//        'PublishMigration',
        'Seed',

    ];

    public function register()
    {
        foreach ($this->commandsList as $command) {
            $this->{'register' . ucfirst($command) . 'Command'}();
        }
        $this->commands(
            'command.module.migrate',
            'command.module.seed',
            'command.module.publishAsset'
        );

    }

    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.module.migrate', function ($app) {

            return new MigrateCommand($app, $app['migrator'], $app['modules']);
        });
    }

    protected function registerSeedCommand()
    {
        $this->app->singleton('command.module.seed', function ($app) {
            return new SeedCommand($app);
        });
    }

    protected function registerPublishAssetCommand()
    {
        $this->app->singleton('command.module.publishAsset', function ($app) {
            return new PublishAssetCommand($app, $app['files'], $app['modules']);
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
