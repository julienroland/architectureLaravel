<?php namespace Cms\Providers;

use Cms\Modules\Module;
use Cms\Modules\ModuleManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;


abstract class BaseModuleServiceProvider extends ServiceProvider
{
    private $file;
    private $config;
    private $translator;
    private $view;
    private $router;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->file = new Filesystem;
        $this->router = $app['router'];
        $this->config = $app['config'];
        $this->view = $app['view'];
        $this->translator = $app['translator'];
    }

    public function register()
    {
    }

    public function boot()
    {
        if ($module = $this->getModule(func_get_args())) {
            $module = $this->getModuleInstance($module);
            $this->package('dogstudio/cms');
            if ($module->active()) {
                $this->loadModule($module);
            }

            $this->package($module->getName(), $module->getName(), $module->getPath());
        }
    }

    private function getModule($args)
    {
        $module = (isset($args[0]) and is_string($args[0])) ? $args[0] : null;
        return $module;
    }

    private function getModuleInstance($module)
    {
        $this->app->bind($module, function ($app) use ($module) {
            return new Module($module, new ModuleManager($app['files']));
        });
        return $this->app[$module];
    }

    private function loadModule($module)
    {
        $this->bindToContainer();
        $this->addConfigs($module);
        $this->getModuleStarter($module);
        $this->registerModulesProviders($module);
        $this->loadRoute($module);
    }

    private function bindToContainer()
    {
        $this->app->bind('modules', function () {
            return new ModuleManager($this->file);
        });
    }

    private function addConfigs($module)
    {
        $this->addConfig($module);
        $this->addView($module);
        $this->addTranslations($module);
    }


    private function getModuleStarter($module)
    {
        if (file_exists($start = $module->getStarterFile())) {
            require $start;
        }
    }

    private function registerModulesProviders($module)
    {
        foreach ($this->file->allFiles($module->getProvidersPath()) as $file) {
            if ($this->file->exists($file)) {
                $filePath = explode('.', $file->getRelativePathname())[0];
                $module->addProvider($this->app, $filePath);
            }
        }
    }

    private function loadRoute($module)
    {
        $routesFile = $module->getRouteFile();
        if ($this->file->exists($routesFile)) {
            $router = $this->router;
            $config = $this->config;
            $app = $this->app;
            require $routesFile;
        }
    }

    private function addConfig($module)
    {
        $this->config->package($module, $module->getConfigPath(),
            lcfirst($module));
    }

    private function addView($module)
    {
        $this->view->addNamespace(lcfirst($module),
            base_path() . '/modules/' . $module . '/Resources/views/');
    }

    private function addTranslations($module)
    {
        $this->translator->addNamespace(lcfirst($module),
            base_path() . '/modules/' . $module . '/Resources/lang/');
    }


    public function registerConsoleCommand($key, $class)
    {
        $key = 'command.' . $key;
        $this->app[$key] = $this->app->share(function ($app) use ($class) {
            return new $class;
        });

        $this->commands($key);
    }

    public function provides()
    {
        return ['modules'];
    }


}
