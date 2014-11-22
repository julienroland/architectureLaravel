<?php namespace Cms\Console\Module\Commands;

use Cms\Console\Module\Traits\BaseModuleCommandTraits;
use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SeedCommand extends Command
{
    protected $name = 'module:seed';

    protected $description = 'Seed module(s)';
    /**
     * @var
     */
    private $app;

    public function __construct($app)
    {
        parent::__construct();
        $this->app = $app;
    }

    use BaseModuleCommandTraits;
    use ModulesTrait;

    public function fire($moduleArgument = null)
    {
        $this->setModuleInArgument($moduleArgument);
        $module = $this->getModuleInArgument();
        if ($module) {
            return $this->seed($module->getName());
        }
        foreach ($this->getModules()->getEnabled() as $module) {
            $this->seed($module->getName());
        }
        return $this->info("All active modules seeded");

    }

    protected function seed($moduleName)
    {
        $params = $this->params($moduleName);
        $params = $this->options($params);
        $this->comment("Seeding module: $moduleName");
        $this->call('db:seed', $params);
        $this->info("Module $moduleName seeded !");
    }

    protected function getArguments()
    {
        return array(
            array('module', InputArgument::OPTIONAL, 'Name of the Module'),
        );
    }

    protected function getOptions()
    {
        return array(
            array('class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', null),
            array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed.'),
            array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'),
        );
    }

    /**
     * @param $name
     * @return array
     */
    protected function params($name)
    {
        $params = [
            '--class' => $this->option('class') ?: $this->getSeeder($name)
        ];
        return $params;
    }

    protected function options($params)
    {
        if ($option = $this->option('database')) {
            $params['--database'] = $option;
            return $params;
        }
        return $params;
    }
}
