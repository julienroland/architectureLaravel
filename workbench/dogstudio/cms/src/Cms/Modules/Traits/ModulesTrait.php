<?php namespace Cms\Modules\Traits;

use Illuminate\Support\Str;

trait ModulesTrait
{

    protected function getSeeder($name)
    {
        $name = $this->getStudly($name);
        return $this->getSeederNamespace($name) . $name . 'DatabaseSeeder';
    }

    protected function getMigration($name)
    {
        $name = $this->getStudly($name);
        return $this->getSeederNamespace($name) . $name . 'DatabaseSeeder';
    }

    protected function getSeederNamespace($name)
    {
        return $name . '\Database\Seeders\\';
    }

    protected function getStudly($name)
    {
        return Str::studly($name);
    }

    protected function getModuleByName($name)
    {
        return $this->app[$name];
    }

    protected function getPublicAssetsDirectory($name)
    {
        return public_path() . '/' . $this->getAssetsPathName() . '/' . $name;
    }

    protected function getAssetsPathName()
    {
        return 'Assets';
    }
}
