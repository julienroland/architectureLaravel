<?php namespace Cms\Providers\Booted;

use Cms\Modules\Module;
use Cms\Modules\ModuleManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{

    private $file;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->file = new Filesystem;
    }

    public function boot()
    {
        $modules = $this->getEnableModulesList();
        $this->registerModulesProviders($modules);
    }

    public function register()
    {
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
