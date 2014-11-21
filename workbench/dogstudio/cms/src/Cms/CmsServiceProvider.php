<?php namespace Cms;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    private $providerPath = 'Providers';
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
        $this->registerFacadeAlias();

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
                $this->app->register(__NAMESPACE__ . '\\' . $this->providerPath . '\\' . $this->bootPath . '\\' . $this->getClassName($file));
            }
        }
    }


    private function registerOnBooted()
    {
        $files = $this->getBootedFolder();
        if (!empty($files)) {
            foreach ($files as $file) {
                $this->app->register(__NAMESPACE__ . '\\' . $this->providerPath . '\\' . $this->bootedPath . '\\' . $this->getClassName($file));
            }
        }
    }

    private function getBootFolder()
    {
        return $this->file->files(__DIR__ . '/' . $this->providerPath . '/' . $this->bootPath);
    }

    private function getBootedFolder()
    {
        return $this->file->files(__DIR__ . '/' . $this->providerPath . '/' . $this->bootedPath);
    }

    private function getClassName($file)
    {
        $list = explode('/', explode('.', $file)[0]);
        $className = $list[count($list) - 1];

        if (!is_null($className) && !empty($className)) {
            return $className;
        }
    }

    private function registerFacadeAlias()
    {
        $files = $this->file->files(__DIR__ . '/Facades');
        if (!empty($files)) {
            foreach ($files as $file) {
                $classNamespace = __NAMESPACE__ . '\\Facades\\' . $this->getClassName($file);
                $facade = new $classNamespace;
                if (isset($facade->alias) && !empty($facade->alias)) {
                    AliasLoader::getInstance([$facade->alias => $classNamespace])->register();
                }
            }
        }
    }


}
