<?php namespace Cms\Console\Install;

use Carbon\Carbon;
use Cms\Console\Install\Factory\UserDriverFactory;
use Dotenv;
use Illuminate\Console\Command;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    protected $name = 'cms:install';

    protected $description = 'Install the CMS';

    protected $userDriverlist = [0 => 'None', 'sentry', 'sentinel (paying)'];

    private $user;

    private $file;

    private $app;
    /**
     * @var
     */
    private $userDriver;

    public function __construct($app, $file, $user, UserDriverFactory $userDriverFactory)
    {
        parent::__construct();
        $this->app = $app;
        $this->file = $file;
        $this->userDriver = $userDriverFactory;
        $this->user = $user;
    }

    public function fire()
    {
        $this->comment('Starting the installation process...');

        $this->configureDatabase();

        $isUserCreated = $this->userDriver();

        $this->publish();

        $this->createFirstUser();

        if ($isUserCreated) {
            $this->blockMessage(
                'Success!',
                'Cms ready! You can now login with your username and password at /backend'
            );
        } else {
            $this->blockMessage(
                'Success!',
                'Cms ready! But you need to install a user driver and create an account'
            );
        }
    }

    protected function createFirstUser()
    {
        $this->line('Creating an Admin user account...');

        $firstname = $this->ask('Enter your first name');
        $lastname = $this->ask('Enter your last name');
        $email = $this->ask('Enter your email address');
        $password = $this->secret('Enter a password');

        $userInfo = [
            'first_name' => ucfirst($firstname),
            'last_name' => ucfirst($lastname),
            'email' => $email,
            'password' => $password,
        ];
        $this->user->createWithRoles($userInfo, ['Admin']);

        $this->info('Admin account created!');
    }

    /**
     * @return array
     */
    protected function getUserDriverList()
    {
        return $this->userDriverlist;
    }

    /**
     * @return bool
     */
    protected function userDriver()
    {
        $userDriverlist = $this->getUserDriverList();

        $driver = $this->choice("Which user driver do you wish use ?", $userDriverlist);

        if (isset($driver) && !empty($driver) && $driver !== 'None') {
            if ($this->userDriver->handle($this, $this->app, $this->file, $driver)) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    private function runUserSentinelCommands()
    {
        $this->runSentinelMigrations();
        $this->runSentinelConfigFile();
        $this->runUserSeeds();
        $this->createFirstUser();

        $this->info('User commands done.');
    }

    /**
     *
     */
    private function runUserSentryCommands()
    {
        $this->comment('Loading Sentry package (can take a bit of time...)');
        echo system('composer require cartalyst/sentry:2.1.*');
        $this->info('Loading done');
//        $this->call('composer require cartalyst/sentry:2.1.*');
        $this->comment('Running Sentry migration...');
        $this->runSentryMigrations();
        $this->comment('Running Sentry configuration...');
        $this->runSentryConfigFile();
        $this->comment('Registering Sentry to the application...');
        $this->app->register('Cartalyst\Sentry\SentryServiceProvider');
        $alias = [
            'Sentry' => 'Cartalyst\Sentry\Facades\Laravel\Sentry',
        ];
        AliasLoader::getInstance($alias)->register();
        $this->setSentryUserEntity();
        $this->comment('Reloading class');
        echo system('composer dump-autoload');

        $this->info('Sentry ready !');

        $this->runUserSeeds();
        $this->createFirstUser();

        $this->info('User commands done.');
    }

    /**
     * Create the first user that'll have admin access
     */


    /**
     * Run migrations specific to Sentinel
     */
    private function runSentinelMigrations()
    {
        $this->call('migrate', ['--package' => 'cartalyst/sentinel']);
    }


    protected function blockMessage($title, $message, $style = 'info')
    {
        $formatter = $this->getHelperSet()->get('formatter');
        $errorMessages = [$title, $message];
        $formattedBlock = $formatter->formatBlock($errorMessages, $style, true);
        $this->line($formattedBlock);
    }

    private function publish()
    {
        $this->call('module:publish');
    }

    /**
     * Configuring the database information
     */
    private function configureDatabase()
    {
        $databaseName = $this->ask('Enter your database name ');
        $databaseUsername = $this->ask('Enter your database username ');
        $databasePassword = $this->secret('Enter your database password ');

        $this->setLaravelConfiguration($databaseName, $databaseUsername, $databasePassword);
        $this->configureEnvironmentFile($databaseName, $databaseUsername, $databasePassword);
    }

    /**
     * Writing the environment file
     * @param $databaseName
     * @param $databaseUsername
     * @param $databasePassword
     */
    private function configureEnvironmentFile($databaseName, $databaseUsername, $databasePassword)
    {
        Dotenv::makeMutable();

        $environmentFile = $this->file->get('.env');
        $this->file->put('.env.old' . Carbon::now()->timestamp, $environmentFile);

        if ($this->laravel['config']['app.key'] == 'YourSecretKey!!!') {
            $this->laravel['config']['app.key'] = $this->getRandomKey();
        }
        $env = [
            "APP_ENV=" . App::environment() . PHP_EOL,
            "APP_KEY=" . $this->laravel['config']['app.key'] . PHP_EOL,
            "DB_NAME=$databaseName" . PHP_EOL,
            "DB_USERNAME=$databaseUsername" . PHP_EOL,
            "DB_PASSWORD=$databasePassword" . PHP_EOL
        ];
//        $newEnvironmentFile = str_replace($search, $replace, $environmentFile);
//        $newEnvironmentFile .= "DB_NAME=$databaseName";

        // Write the new environment file
        $this->file->put('.env', $env);
        // Delete the old environment file
//        $this->file->delete('env.example');

        $this->info('Environment file written');

        Dotenv::makeImmutable();
    }

    protected function getRandomKey()
    {
        return Str::random(32);
    }

    /**
     * Set DB credentials to laravel config
     * @param $databaseName
     * @param $databaseUsername
     * @param $databasePassword
     */
    private function setLaravelConfiguration($databaseName, $databaseUsername, $databasePassword)
    {
        $this->laravel['config']['database.connections.mysql.database'] = $databaseName;
        $this->laravel['config']['database.connections.mysql.username'] = $databaseUsername;
        $this->laravel['config']['database.connections.mysql.password'] = $databasePassword;
    }


    private function runSentinelConfigFile()
    {
        $path = 'Modules/User/Config/userdriver.php';
        $string = "<?php return [
            'driver'=>'Sentinel',
            'seeder'=>[
                'SentryGroupSeedTableSeeder',
                'SentryUserSeedTableSeeder'
            ]
        ];";
        $file = $this->file->put($path, $string);

        if ($file) {
            $this->info('User driver define in config file !');
        }
    }


}
