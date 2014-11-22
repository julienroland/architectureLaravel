<?php  namespace Cms\Console\Install\UserDrivers;

use Illuminate\Foundation\AliasLoader;

class SentryUserDriverCommand extends AbstractUserDriver
{
    private $app;
    private $command;
    private $file;

    public function __construct($command, $app, $file)
    {
        parent::__construct($command);
        $this->command = $command;
        $this->app = $app;
        $this->file = $file;
    }

    public function generate()
    {
        $this->command->comment('Loading Sentry package (can take a bit of time...)');
        echo system('composer require cartalyst/sentry:2.1.* --no-update');
        $this->command->info('Loading done');
        $this->command->comment('Running Sentry migration...');
        $this->runMigrations();
        $this->command->comment('Running Sentry configuration...');
        $this->runConfigFile();
        $this->command->comment('Registering Sentry to the application...');
        $this->app->register('Cartalyst\Sentry\SentryServiceProvider');
        $alias = [
            'Sentry' => 'Cartalyst\Sentry\Facades\Laravel\Sentry',
        ];
        AliasLoader::getInstance($alias)->register();
        $this->setUserEntity();
        $this->command->comment('Reloading class');
        echo system('composer dump-autoload');

        $this->command->info('Sentry ready !');

        $this->command->info('User commands done.');

        return true;
    }

    private function setUserEntity()
    {
        $entity = '<?php namespace User\Entities;

        use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;
        use Laracasts\Presenter\PresentableTrait;

        class User extends SentryUser
        {
            use PresentableTrait;

            protected $fillable = [
                "email",
                "password",
                "permissions",
                "first_name",
                "last_name"
            ];

            protected $presenter = "User\\\Presenters\\\UserPresenter";
        }
        ';
        $this->file->put('modules/User/Entities/User.php', $entity);
    }

    private function runMigrations()
    {
        $this->command->call('migrate', ['--package' => 'cartalyst/sentry']);
    }

    private function runConfigFile()
    {
        $path = 'modules/User/Config/userdriver.php';
        $string = "<?php return " . PHP_EOL . "[" . PHP_EOL . "'driver'=>'Sentry'," . PHP_EOL . "'seeder'=>" . PHP_EOL . "[" . PHP_EOL . "'SentryGroupSeedTableSeeder'," . PHP_EOL . "'SentryUserSeedTableSeeder'" . PHP_EOL . "]" . PHP_EOL . "];";
        $file = $this->file->put($path, $string);

        if ($file) {
            $this->command->info('User driver define');
        }
        $this->command->comment('Publishing Sentry config');
        $this->command->call('publish:config', ['package' => 'cartalyst/sentry']);
    }

}
