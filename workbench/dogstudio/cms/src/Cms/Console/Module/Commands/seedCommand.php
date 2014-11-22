<?php namespace Cms\Console\Module\Commands;

use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SeedCommand extends Command
{
    protected $name = 'module:seed';

    protected $description = 'Seed module(s)';

    use BaseModuleCommandTraits;
    use ModulesTrait;

    public function fire($moduleArgument = null)
    {
        $this->setModuleInArgument($moduleArgument);
        $module = $this->getModuleInArgument();
        if ($module) {
            $this->seed($module);
        } else {
            foreach ($this->getModules()->getEnabled() as $module) {
                $this->seed($module);
            }
            return $this->info("All active modules seeded");
        }
    }

    protected function seed($name)
    {
        $params = $this->params($name);
        $params = $this->options($params);
        $this->comment("Seeding module: $name");
        $this->call('db:seed', $params);
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
