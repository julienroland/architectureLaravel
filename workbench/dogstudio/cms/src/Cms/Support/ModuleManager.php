<?php  namespace Cms\Support;

use Cms\Exceptions\NotFoundException;

class ModuleManager
{
    private $path = 'modules';
    private $assetPath = 'Assets';
    private $config;

    public function __construct($config, $file)
    {
        $this->config = $config;
        $this->file = $file;
    }

    public function all()
    {
        $directories = $this->file->directories(base_path() . '/modules');
        $this->hasNotModule($directories);
        return $directories;
    }

    public function asset($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        $this->isNotExist($asset);
        echo $asset;
    }

    public function style($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        $this->isNotExist($asset);
        echo $this->addInStyleTag($asset);
    }

    public function script($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        $this->isNotExist($asset);
        echo $this->addInScriptTag($asset);
    }

    private function getAsset($module = '', $asset = '')
    {
        return str_replace([':module', ':asset'], [$module, $asset],
            base_path() . '/' . $this->path . '/' . ucfirst(':module') . '/' . $this->assetPath . '/' . ':asset');
    }

    private function isNotExist($asset)
    {
        if (!$this->file->exists($asset)) {
            throw new NotFoundException("Asset {$asset} not found");
        }
    }

    private function addInStyleTag($asset)
    {
        return '<link rel="stylesheet" type="text/css" href="' . $asset . '">';
    }

    private function addInScriptTag($asset)
    {
        return '<script type="text/javascript" src="' . $asset . '"></script>';
    }

    private function hasNotModule($directories)
    {
        if (empty($directories)) {
            throw new NoModuleRegister("No module are register");
        }
    }
}
