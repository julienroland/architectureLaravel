<?php  namespace Cms\Support;

use Cms\Exceptions\NotFoundException;

class Module
{
    private $path = 'modules';
    private $assetPath = 'Assets';
    private $config;

    public function __construct($config, $file)
    {
        $this->config = $config;
        $this->file = $file;
    }

    public function asset($module, $asset)
    {
        $asset = $this->getAsset($module, $asset);
        $this->isNotExist($asset);
        echo $asset;
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
}
