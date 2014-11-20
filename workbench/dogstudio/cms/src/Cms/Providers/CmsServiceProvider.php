<?php namespace Cms\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    private $bootPath = 'Boot';
    private $bootedPath = 'Booted';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->file = new Filesystem;
        $this->app = $app;
    }

    protected $defer = false;

    public function boot()
    {
        $this->registerOnBooted();
    }

    public function register()
    {
        $this->registerOnBoot();

    }

    public function provides()
    {
        return [];
    }

    private function registerOnBoot()
    {
        $files = $this->getBootFolder();
        if (!empty($files)) {
            foreach ($files as $file) {
                $this->app->register(__NAMESPACE__ . '\\' . $this->bootPath . '\\' . $this->getClassName($file));
            }
        }
    }


    private function registerOnBooted()
    {
        $files = $this->getBootedFolder();
        if (!empty($files)) {
            foreach ($files as $file) {
                $this->app->register(__NAMESPACE__ . '\\' . $this->bootedPath . '\\' . $this->getClassName($file));
            }
        }
    }

    private function getBootFolder()
    {
        return $this->file->files(__DIR__ . '/' . $this->bootPath);
    }

    private function getBootedFolder()
    {
        return $this->file->files(__DIR__ . '/' . $this->bootedPath);
    }

    private function getClassName($file)
    {
        $list = explode('/', explode('.', $file)[0]);
        $className = $list[count($list) - 1];

        if (!is_null($className) && !empty($className)) {
            return $className;
        }
    }


}
