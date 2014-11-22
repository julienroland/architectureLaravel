<?php namespace Cms\Console\Module\Traits;

use Illuminate\Support\Str;

trait BaseModuleCommandTraits
{
    protected function getModules()
    {
        return $this->laravel['modules'];
    }

    protected function getModuleInArgument()
    {
        return $this->module;
    }

    protected function globalInfo($module, $action)
    {
        $this->info($this->globalWrite($module, $action));
    }

    protected function globalWrite($module, $action)
    {
        return "---------------   Module: $module $action   --------------- " . PHP_EOL;
    }

    protected function setModuleInArgument($moduleArgument = null)
    {
        if (is_null($moduleArgument)) {
            $moduleArgument = Str::studly($this->argument('module'));
        }
        $this->module = $this->app[$moduleArgument];
    }

    protected function seedModuleCommand()
    {
        $this->call('module:seed ' . $this->module, ['--force' => true]);
    }
}
