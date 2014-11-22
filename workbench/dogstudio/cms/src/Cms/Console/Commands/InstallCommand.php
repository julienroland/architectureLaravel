<?php namespace Cms\Console\Commands;

use Carbon\Carbon;
use Dotenv;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use User\Repositories\UserRepository;

class InstallCommand extends Command
{

    protected $name = 'cms:install';

    protected $description = 'Install the CMS';

    private $user;

    private $file;

    private $app;

    public function __construct($app, $file, $user)
    {
        parent::__construct();
        $this->app = $app;
        $this->user = $user;
        $this->file = $file;
    }

    public function fire()
    {
        $this->comment('Starting the installation process...');
        $this->configureDatabase();
        $userDriverlist = [0 => 'None', 'sentry', 'sentinel (paying)'];
        $driver = $this->choice("Which user driver do you wish use ?", $userDriverlist);
        if (isset($driver) && !empty($driver) && $driver !== 'None') {
            $this->{'runUser' . $driver . 'Commands'}();
            $isUserCreated = true;
        } else {
            $isUserCreated = false;
        }

        $this->runMigrations();

//        $this->publishAssets();
        if ($isUserCreated) {
            $this->blockMessage(
                'Success!',
                'Platform ready! You can now login with your username and password at /backend'
            );
        } else {
            $this->blockMessage(
                'Success!',
                'Platform ready! But you need to install a user driver and create an account'
            );
        }
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
    private function createFirstUser()
    {
        $this->line('Creating an Admin user account...');

        $firstname = $this->ask('Enter your first name');
        $lastname = $this->ask('Enter your last name');
        $email = $this->ask('Enter your email address');
        $password = $this->secret('Enter a password');

        $userInfo = [
            'first_name' => $firstname,
            'last_name' => $lastname,
            'email' => $email,
            'password' => $password,
        ];
        $this->user->createWithRoles($userInfo, ['Admin']);

        $this->info('Admin account created!');
    }

    /**
     * Run migrations specific to Sentinel
     */
    private function runSentinelMigrations()
    {
        $this->call('migrate', ['--package' => 'cartalyst/sentinel']);
    }

    /**
     * Run the migrations
     */
    private function runMigrations()
    {
        $this->call('module:migrate', ['module' => 'Setting']);

        $this->info('Application migrated!');
    }

    private function runUserSeeds()
    {
        $this->call('module:seed', ['module' => 'User']);
    }

    /**
     * Symfony style block messages
     * @param $title
     * @param $message
     * @param string $style
     */
    protected function blockMessage($title, $message, $style = 'info')
    {
        $formatter = $this->getHelperSet()->get('formatter');
        $errorMessages = [$title, $message];
        $formattedBlock = $formatter->formatBlock($errorMessages, $style, true);
        $this->line($formattedBlock);
    }

    /**
     * Publish the CMS assets
     */
    private function publishAssets()
    {
        $this->call('module:publish', ['module' => 'Core']);
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

    private function runSentryMigrations()
    {
        $this->call('migrate', ['--package' => 'cartalyst/sentry']);
    }

    private function runSentryConfigFile()
    {
        $path = 'modules/User/Config/userdriver.php';
        $string = "<?php return " . PHP_EOL . "[" . PHP_EOL . "'driver'=>'Sentry'," . PHP_EOL . "'seeder'=>" . PHP_EOL . "[" . PHP_EOL . "'SentryGroupSeedTableSeeder'," . PHP_EOL . "'SentryUserSeedTableSeeder'" . PHP_EOL . "]" . PHP_EOL . "];";
        $file = $this->file->put($path, $string);

        if ($file) {
            $this->info('User driver define');
        }
        $this->comment('Publishing Sentry config');
        $this->call('publish:config', ['package' => 'cartalyst/sentry']);
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

    private function setSentryUserEntity()
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

}
