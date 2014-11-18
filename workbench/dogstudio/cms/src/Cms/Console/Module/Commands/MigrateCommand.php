<?php namespace Cms\Console\Module\Commands;

use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class MigrateCommand extends Command
{
    use ModulesTrait;

    /**
     * @var
     */
    private $migrator;

    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
    }

    protected $name = 'module:migrate';

    protected $description = 'Migrate a module';

    public function fire()
    {
        $this->module = $this->laravel['modules'];
        $module = Str::studly($this->argument('module'));
        if ($module) {
            $this->comment("Migrating module: $module ...");
            $this->call('migrate', $this->getParameter($module));
            return $this->info("Module $module migrated.");
        }
        foreach ($this->module->all() as $name) {
            $this->dbSeed($name);
        }
        return $this->info("All modules seeded.");
    }

    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->input->getOption('database'));

        if ( ! $this->migrator->repositoryExists())
        {
            $options = array('--database' => $this->input->getOption('database'));

            $this->call('migrate:install', $options);
        }
    }

    protected function getArguments()
    {
        return array(
            array('module', InputArgument::OPTIONAL, 'Name of the Module'),
        );
    }

    protected function getParameter($name)
    {
        $params = array();
        $params['--path'] = $this->getMigration($name);
        if ($option = $this->option('database')) {
            $params['--database'] = $option;
        }
        if ($option = $this->option('pretend')) {
            $params['--pretend'] = $option;
        }
        if ($option = $this->option('force')) {
            $params['--force'] = $option;
        }
        return $params;
    }
}
