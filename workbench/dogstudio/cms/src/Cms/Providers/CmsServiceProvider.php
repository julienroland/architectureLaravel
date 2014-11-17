<?php namespace Cms\Providers;

use Cms\Support\Module\Module;
use Cms\Support\ModuleManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    public function __construct($app)
    {
        parent::__construct($app);
        $this->file = new Filesystem;
        $this->app = $app;
    }

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $modules = $this->getEnableModulesList();
        $this->registerModulesProviders($modules);
    }

    public function register()
    {

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function registerModulesProviders($modules)
    {
        foreach ($modules as $module) {
            if ($module->hasLauncher()) {
                $module->register($this->app);
            }
        }
    }

    public function getModuleInstance($module)
    {
        return new Module($this->getModuleName($module), new ModuleManager($this->file));
    }

    private function getEnableModulesList()
    {
        $modules = [];
        $modulesList = $this->file->directories(base_path() . '/modules/');
        foreach ($modulesList as $module) {
            $module = $this->getModuleInstance($module);
            if ($module->active()) {
                $modules[] = $module;
            }
        }
        return $modules;

    }

    private function getModuleName($module)
    {
        $ex = explode('/', $module);
        $nb = count($ex);
        return $ex[$nb - 1];
    }

}
