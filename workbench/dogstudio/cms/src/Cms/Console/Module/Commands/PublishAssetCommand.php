<?php namespace Cms\Console\Module\Commands;

use Cms\Exceptions\NotFoundException;
use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class PublishAssetCommand extends Command
{
    use ModulesTrait;

    /**
     * @var
     */
    private $app;
    /**
     * @var
     */
    private $file;
    /**
     * @var
     */
    private $module;

    public function __construct($app, $file, $module)
    {
        parent::__construct();
        $this->app = $app;
        $this->file = $file;
        $this->module = $module;
    }

    protected $name = 'module:publishAsset';

    protected $description = 'Publish asset from module(s)';

    public function fire()
    {
        $this->module = $this->laravel['modules'];
        $module = Str::studly($this->argument('module'));
        if ($module) {
            $this->comment("Module $module will be published...");
            $module = $this->getModuleByName($module);
            $this->publish($module);
        } else {
            $this->publishAll();
        }
    }

    protected function publish($module)
    {
        $assetDir = $module->getAssetsPath();
        if (!$this->file->isDirectory($assetDir)) {
            return $this->error("Error:: Can't find assets directory for module {$module->getName()}");
        }
        $destinationDir = $this->getPublicAssetDirectory($module->getName());

        $this->comment("Publishing module: $module");

        if ($this->file->copyDirectory($assetDir, $destinationDir)) {
            $this->info("Module {$module->getName()} published");
        } else {
            $this->error("Error:: Module {$module->getName()} not published");
        }

    }

    private function publishAll()
    {
        foreach ($this->module->all() as $module) {
            if ($module->active()) {
                $this->publish($module);
            }
        }
        return $this->info("All active modules published");
    }

    private function getPublicAssetDirectory($name)
    {
        $destinationPath = $this->getPublicAssetsDirectory($name);
        if (!$this->file->isDirectory($destinationPath)) {
            $this->file->makeDirectory($destinationPath, 0775, true);
        }
        return $destinationPath;
    }

    protected function getArguments()
    {
        return array(
            array('module', InputArgument::OPTIONAL, 'Name of the Module'),
        );
    }


}
