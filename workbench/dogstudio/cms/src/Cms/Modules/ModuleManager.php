<?php  namespace Cms\Modules;

use Cms\Exceptions\NotFoundException;
use Cms\Modules\Module;

class ModuleManager
{
    private $path = 'modules';
    private $assetPath = 'Assets';
    private $activeStatus = 1;
    private $inactiveStatus = 0;
    private $config;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function all()
    {
        $modules = [];
        $directories = $this->file->directories(base_path() . '/modules');
        $this->hasNotModule($directories);
        foreach ($directories as $module) {
            $modules[] = new Module(basename($module), $this);
        }
        return $modules;
    }

    public function getEnabled()
    {
        return $this->filterByStatus($this->activeStatus);
    }

    public function getDisabled()
    {
        return $this->filterByStatus($this->inactiveStatus);
    }

    public function asset($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        echo $asset;
    }

    public function style($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        echo $this->addInStyleTag($asset);
    }

    public function script($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        echo $this->addInScriptTag($asset);
    }

    private function getAsset($module = '', $asset = '')
    {
        return str_replace([':module', ':asset'], [$module, $asset],
            '/' . $this->assetPath . '/' . ucfirst(':module') . '/' . ':asset');
    }

    private function isNotExist($asset)
    {
        if (!$this->file->exists($asset)) {
            throw new NotFoundException("Asset {$asset} not found");
        }
    }

    private function addInStyleTag($asset)
    {
        return '<link rel="stylesheet" type="text/css" href="' . $asset . '.css">';
    }

    private function addInScriptTag($asset)
    {
        return '<script type="text/javascript" src="' . $asset . '.js"></script>';
    }

    private function hasNotModule($directories)
    {
        if (empty($directories)) {
            throw new NoModuleRegister("No module are register");
        }
    }

    private function filterByStatus($activeStatus)
    {
        $modules = [];
        if ($activeStatus === 1) {
            foreach ($this->all() as $module) {
                if ($module->active()) {
                    $modules[] = $module;
                }
            }
        } else {
            foreach ($this->all() as $module) {
                if ($module->inactive()) {
                    $modules[] = $module;
                }
            }
        }
        return $modules;
    }
}
