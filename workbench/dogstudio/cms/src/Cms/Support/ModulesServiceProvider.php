<?php namespace Cms\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;


abstract class ModulesServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     * @return void
     */
    public function boot()
    {
        if ($module = $this->getModule(func_get_args())) {

            /*
             * Register paths for: config, translator, view
             */
            $this->package($module, $module, base_path() . '/modules/' . $module);
        }
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {

        if ($module = $this->getModule(func_get_args())) {
            /* *
             * Add Config
             *
             * */
            $this->app['config']->package($module, base_path() . '/modules/' . $module . '/Config', lcfirst($module));
            $this->app['view']->addNamespace(lcfirst($module),
                base_path() . '/modules/' . $module . '/Resources/views/');

            Lang::addNamespace(lcfirst($module), base_path() . '/modules/' . $module . '/Resources/lang/');

            $this->app->bind('module', function()
            {
                return new ModuleManager($this->app['config'], new Filesystem);
            });
            $this->app['module']->all();
            /*
             * Add routes, if available
             */
            $routesFile = base_path() . '/modules/' . $module . '/Http/routes.php';
            if (file_exists($routesFile)) {

                $router = $this->app['router'];
                $config = $this->app['config'];
                $app = $this->app;
                require $routesFile;
            }
        }
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function getModule($args)
    {
        $module = (isset($args[0]) and is_string($args[0])) ? $args[0] : null;
        return $module;
    }

    /**
     * Registers a new console (artisan) command
     * @param $key   The command name
     * @param $class The command class
     * @return void
     */
    public function registerConsoleCommand($key, $class)
    {
        $key = 'command.' . $key;
        $this->app[$key] = $this->app->share(function ($app) use ($class) {
            return new $class;
        });

        $this->commands($key);
    }
}
