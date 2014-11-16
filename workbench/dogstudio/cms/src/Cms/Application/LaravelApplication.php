<?php namespace Cms\Application;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\EnvironmentDetector;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Translation\TranslationServiceProvider;

class LaravelApplication extends Application
{

    /**
     * The Laravel framework version.
     *
     * @var string
     */
    const VERSION = '5.0-dev';

    /**
     * The base path for the Laravel installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The array of booting callbacks.
     *
     * @var array
     */
    protected $bootingCallbacks = array();

    /**
     * The array of booted callbacks.
     *
     * @var array
     */
    protected $bootedCallbacks = array();

    /**
     * All of the registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = array();

    /**
     * The names of the loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = array();

    /**
     * The deferred services and their providers.
     *
     * @var array
     */
    protected $deferredServices = array();

    /**
     * Create a new Illuminate application instance.
     *
     * @param  string|null $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);
//        $this->registerBaseBindings();
//
//        $this->registerBaseServiceProviders();
//
//        $this->registerCoreContainerAliases();
//
//        if ($basePath) $this->setBasePath($basePath);
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance('Illuminate\Container\Container', $this);
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));

        $this->register(new RoutingServiceProvider($this));

        $this->register(new TranslationServiceProvider($this));
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param  array $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }

        $this->hasBeenBootstrapped = true;
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Set the base path for the application.
     *
     * @param  string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return $this
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());

        foreach (['base', 'config', 'database', 'lang', 'public', 'storage'] as $path) {
            $this->instance('path.' . $path, $this->{$path . 'Path'}());
        }
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath . '/app';
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath()
    {
        return $this->basePath . '/config';
    }

    /**
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->basePath . '/database';
    }

    /**
     * Get the path to the language files.
     *
     * @return string
     */
    public function langPath()
    {
        return $this->basePath . '/resources/lang';
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->basePath . '/public';
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->basePath . '/storage';
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     * @return string
     */
    public function environment()
    {
        if (func_num_args() > 0) {
            if (is_array(func_get_arg(0))) {
                return in_array($this['env'], func_get_arg(0));
            } else {
                return in_array($this['env'], func_get_args());
            }
        }

        return $this['env'];
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this['env'] == 'local';
    }

    /**
     * Detect the application's current environment.
     *
     * @param  \Closure $callback
     * @return string
     */
    public function detectEnvironment(Closure $callback)
    {
        $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;
        return $this['env'] = (new EnvironmentDetector())->detect($callback, $args);
    }


    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);
        $this['translator']->setLocale($locale);

        $this['events']->fire('locale.changed', array($locale));
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        $aliases = array(
            'app' => [
                'Illuminate\Foundation\Application',
                'Illuminate\Contracts\Container\Container',
                'Illuminate\Contracts\Foundation\Application'
            ],
            'artisan' => ['Illuminate\Console\Application', 'Illuminate\Contracts\Console\Application'],
            'auth' => 'Illuminate\Auth\AuthManager',
            'auth.driver' => ['Illuminate\Auth\Guard', 'Illuminate\Contracts\Auth\Guard'],
            'auth.password.tokens' => 'Illuminate\Auth\Passwords\TokenRepositoryInterface',
            'blade.compiler' => 'Illuminate\View\Compilers\BladeCompiler',
            'cache' => ['Illuminate\Cache\CacheManager', 'Illuminate\Contracts\Cache\Factory'],
            'cache.store' => ['Illuminate\Cache\Repository', 'Illuminate\Contracts\Cache\Repository'],
            'config' => ['Illuminate\Config\Repository', 'Illuminate\Contracts\Config\Repository'],
            'cookie' => [
                'Illuminate\Cookie\CookieJar',
                'Illuminate\Contracts\Cookie\Factory',
                'Illuminate\Contracts\Cookie\QueueingFactory'
            ],
            'encrypter' => ['Illuminate\Encryption\Encrypter', 'Illuminate\Contracts\Encryption\Encrypter'],
            'db' => 'Illuminate\Database\DatabaseManager',
            'events' => ['Illuminate\Events\Dispatcher', 'Illuminate\Contracts\Events\Dispatcher'],
            'files' => 'Illuminate\Filesystem\Filesystem',
            'filesystem' => 'Illuminate\Contracts\Filesystem\Factory',
            'filesystem.disk' => 'Illuminate\Contracts\Filesystem\Filesystem',
            'filesystem.cloud' => 'Illuminate\Contracts\Filesystem\Cloud',
            'hash' => 'Illuminate\Contracts\Hashing\Hasher',
            'translator' => ['Illuminate\Translation\Translator', 'Symfony\Component\Translation\TranslatorInterface'],
            'log' => ['Illuminate\Log\Writer', 'Illuminate\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
            'mailer' => [
                'Illuminate\Mail\Mailer',
                'Illuminate\Contracts\Mail\Mailer',
                'Illuminate\Contracts\Mail\MailQueue'
            ],
            'paginator' => 'Illuminate\Pagination\Factory',
            'auth.password' => ['Illuminate\Auth\Passwords\PasswordBroker', 'Illuminate\Contracts\Auth\PasswordBroker'],
            'queue' => [
                'Illuminate\Queue\QueueManager',
                'Illuminate\Contracts\Queue\Factory',
                'Illuminate\Contracts\Queue\Monitor'
            ],
            'queue.connection' => 'Illuminate\Contracts\Queue\Queue',
            'redirect' => 'Illuminate\Routing\Redirector',
            'redis' => ['Illuminate\Redis\Database', 'Illuminate\Contracts\Redis\Database'],
            'request' => 'Illuminate\Http\Request',
            'router' => ['Illuminate\Routing\Router', 'Illuminate\Contracts\Routing\Registrar'],
            'session' => 'Illuminate\Session\SessionManager',
            'session.store' => [
                'Illuminate\Session\Store',
                'Symfony\Component\HttpFoundation\Session\SessionInterface'
            ],
            'url' => ['Illuminate\Routing\UrlGenerator', 'Illuminate\Contracts\Routing\UrlGenerator'],
            'validator' => ['Illuminate\Validation\Factory', 'Illuminate\Contracts\Validation\Factory'],
            'view' => ['Illuminate\View\Factory', 'Illuminate\Contracts\View\Factory'],
        );

        foreach ($aliases as $key => $aliases) {
            foreach ((array)$aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }


}
