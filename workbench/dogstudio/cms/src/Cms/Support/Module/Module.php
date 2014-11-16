<?php namespace Cms\Support\Module;

use Cms\Exceptions\NotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Fluent;

class Module
{

    private $name;
    private $manager;

    public function __construct($name, $manager)
    {
        $this->name = $name;
        $this->manager = $manager;
        $this->file = new Filesystem;
        $this->path = $this->getPath($this->name);
    }

    /**
     * Set modules repository instance.
     *
     * @param Repository $repository
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;


        return $this;
    }

    /**
     * Get module repository instance.
     *
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Getter for "name".
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get module name in lowercase.
     *
     * @return string
     */
    public function getLowerName()
    {
        return strtolower($this->name);
    }


    /**
     * Get extra path for specific module.
     *
     * @return string
     */
    public function getExtraPath($extra)
    {
        return $this->getPath() . '/' . $extra;
    }

    /**
     * Delete module.
     *
     * @return void
     */
    public function delete()
    {
        $this->repository->getFiles()->deleteDirectory($this->getPath(), true);
    }


    public function active()
    {
        if ($this->getModuleConfig()->getStatus() <= 0) {
            return false;
        }
        return true;

    }

    public function inactive()
    {
        if ($this->getModuleConfig()->getStatus() <= 0) {
            return true;
        }
        return false;
    }

    /**
     * Enable the current module.
     *
     * @return bool
     */
    public function enable()
    {
        return $this->repository->enable($this->name);
    }

    /**
     * Disable the current module.
     *
     * @return bool
     */
    public function disable()
    {
        return $this->repository->disable($this->name);
    }

    /**
     * Determinte whether the current module disabled.
     *
     * @return bool
     */
    public function notActive()
    {
        return !$this->active();
    }

    /**
     * Get the module presenter class instance.
     *
     * @return \Pingpong\Modules\Presenter
     */
    public function present()
    {
        return new Presenter($this);
    }


    public function toJson($string)
    {
        return $this->fluent->toJson($string);
    }

    public function decodeJson($string)
    {
        return json_decode($string);
    }

    public function toArray($string)
    {
        return $this->fluent->toArray($string);
    }

    /**
     * Get start filepath.
     *
     * @return string
     */
    public function getStartFilePath()
    {
        return $this->getPath() . '/start.php';
    }

    /**
     * Get start json path.
     *
     * @return string
     */
    public function getJsonPath()
    {
        return $this->getPath() . '/modules.json';
    }

    /**
     * Register the start file from current module.
     *
     * @return string
     */
    public function register()
    {
        include_once $this->getStartFilePath();
    }

    /**
     * Handle call to __toString method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    public function getPath()
    {
        return base_path() . '/modules/' . $this->name . '/';
    }

    public function getConfigPath()
    {
        return base_path() . '/modules/' . $this->name . '/Config';
    }

    public function getStarterFile()
    {
        return base_path() . '/modules/' . $this->name . '/start.php';
    }

    public function getProvidersPath()
    {
        return base_path() . '/modules/' . $this->name . '/Providers';
    }

    public function getRouteFile()
    {
        return base_path() . '/modules/' . $this->name . '/Http/routes.php';
    }

    public function addProvider($app, $providerPath)
    {
        $app->register('\\' . $this->name . '\\Providers\\' . $providerPath);
    }

    private function getModuleConfig()
    {
        if (!$this->file->exists($this->path . 'module.json')) {
            throw new NotFoundException("File module.json from module: {$this->name} not found");
        }

        $this->config = $this->decodeJson($this->file->get($this->path . 'module.json'));
        return $this;
    }

    private function getStatus()
    {
        return $this->config->active;
    }

}
