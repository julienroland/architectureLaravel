<?php namespace Cms\Console\Module\Commands;

use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class SeedCommand extends Command
{
    use ModulesTrait;

    protected $name = 'module:seed';

    protected $description = 'Seed module(s)';

    public function fire()
    {
        $this->module = $this->laravel['modules'];
        $module = Str::studly($this->argument('module'));
        if ($module) {
            $this->comment("Module $module will be seeded...");
            $this->dbSeed($module);
        } else {
            foreach ($this->module->all() as $module) {
                if ($module->active()) {
                    $this->dbSeed($module->getName());
                }
            }
            return $this->info("All active modules seeded");
        }
    }

    protected function dbSeed($name)
    {
        $params = [
            '--class' => $this->option('class') ?: $this->getSeeder($name)
        ];
        if ($option = $this->option('database')) {
            $params['--database'] = $option;
        }
        $this->comment("Seeding module: $name .");
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
}
