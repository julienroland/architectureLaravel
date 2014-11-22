<?php namespace Cms\Providers\Boot;

use Cms\Console\Commands\InstallCommand;
use Cms\Console\Module\Commands\MigrateCommand;
use Cms\Console\Module\Commands\PublishAssetCommand;
use Cms\Console\Module\Commands\PublishCommand;
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
        'Install',
//        'List',
        'Migrate',
//        'MigrateRefresh',
//        'MigrateReset',
//        'MigrateRollback',
//        'Migration',
//        'Model',
        'PublishAsset',
        'Publish',
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
            'command.module.publishAsset',
            'command.module.publish',
            'command.module.install'
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

    protected function registerPublishCommand()
    {
        $this->app->singleton('command.module.publish', function ($app) {
            return new PublishCommand(
                $app['command.module.migrate'],
                $app['command.module.seed'],
                $app['command.module.publishAsset']);
        });
    }

    protected function registerInstallCommand()
    {
        $this->app->singleton('command.module.install', function ($app) {
            return new InstallCommand($app, $app['files'], $app['User\Repositories\UserRepository']);
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
