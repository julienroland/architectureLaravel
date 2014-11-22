<?php namespace Cms\Console\Module\Commands;

use Cms\Console\Module\Traits\BaseModuleCommandTraits;
use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command
{
    private $assetCommand;

    private $migrateCommand;

    private $seedCommand;

    protected $name = 'module:publish';

    protected $description = 'Publish module(s) assets, migration and seed';

    public function __construct(
        MigrateCommand $migrateCommand,
        SeedCommand $seedCommand,
        PublishAssetCommand $assetCommand
    ) {
        parent::__construct();
        $this->assetCommand = $assetCommand;
        $this->migrateCommand = $migrateCommand;
        $this->seedCommand = $seedCommand;
    }

    use BaseModuleCommandTraits;
    use ModulesTrait;

    public function fire()
    {
        $moduleArgument = $this->argument('module');
        if (isset($moduleArgument)) {
            return $this->publish($moduleArgument);
        }
        foreach ($this->getModules()->allEnabled() as $moduleArgument) {
            $this->publish($moduleArgument->getName());

        }
    }

    private function publish($moduleArgument)
    {
        $this->comment("Publish all stuff of module: $moduleArgument");

        $this->call('module:migrate', ['module' => $moduleArgument]);
        $this->call('module:seed', ['module' => $moduleArgument]);
        $this->call('module:publishAsset', ['module' => $moduleArgument]);

        $this->globalInfo($moduleArgument, 'PUBLISHED');
    }

    protected function getArguments()
    {
        return array(
            array('module', InputArgument::OPTIONAL, 'Name of the Module'),
        );
    }

}
