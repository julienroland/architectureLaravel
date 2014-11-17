<?php namespace Cms\Modules\Traits;

use Illuminate\Support\Str;

trait ModulesTrait
{
    private $string;

//    public function __construct(Str $string)
//    {
//        $this->string = $string;
//    }

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
}
