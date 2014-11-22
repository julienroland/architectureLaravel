<?php namespace Cms\Console\Module\Commands;

use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PublishAssetCommand extends Command
{
    private $app;

    private $file;

    private $module;

    protected $description = 'Publish asset from module(s)';

    public function __construct($app, $file, $module)
    {
        parent::__construct();
        $this->app = $app;
        $this->file = $file;
        $this->module = $module;
    }

    use BaseModuleCommandTraits;
    use ModulesTrait;

    public function fire($moduleArgument = null)
    {
        $this->setModuleInArgument($moduleArgument);
        $module = $this->getModuleInArgument();
        if ($module) {
            return $this->publish($module);
        }
        foreach ($this->getModules()->getEnabled() as $module) {
            $this->publish($module);
        }
        return $this->info("All active modules published");
    }

    protected function publish($module)
    {
        $assetDir = $module->getAssetsPath();
        if (!$this->file->isDirectory($assetDir)) {
            return $this->error("Error:: Can't find assets directory for module {$module->getName()}");
        }
        //public/Assets/...
        $destinationDir = $this->getPublicAssetDirectory($module->getName());

        $this->comment("Publishing module: $module");

        //Copy assets of a module to his public path
        if ($this->file->copyDirectory($assetDir, $destinationDir)) {
            $this->info("Module {$module->getName()} published");
        } else {
            $this->error("Error:: Module {$module->getName()} not published");
        }

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
