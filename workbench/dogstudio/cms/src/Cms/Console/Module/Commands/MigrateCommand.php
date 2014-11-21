<?php namespace Cms\Console\Module\Commands;

use Cms\Modules\Module;
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
    /**
     * @var Module
     */
    private $module;

    public function __construct($app, Migrator $migrator, $module)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->app = $app;
        $this->modules = $module;
    }

    protected $name = 'module:migrate';

    protected $description = 'Migrate module(s)';

    public function fire()
    {
        $this->module = $this->laravel['modules'];
        $module = Str::studly($this->argument('module'));
        if ($module) {
            $pretend = $this->input->getOption('pretend');
            $path = $this->app[$module]->getMigrationPath();
            $this->comment("Migrating module: $module ...");
            $this->migrator->run($path, $pretend);
            foreach ($this->migrator->getNotes() as $note) {
                $this->comment($note);
            }
            if ($this->input->getOption('seed')) {
                $this->call('module:seed' . $module, ['--force' => true]);
            }
//            $this->call('migrate', $this->getParameter($module));
            return $this->info("Module $module migrated !");
        } else {
            foreach ($this->modules->getEnabled() as $module) {
                $pretend = $this->input->getOption('pretend');
                $path = $module->getMigrationPath();
                $this->comment("Migrating module: {$module->getName()} ...");
                $this->migrator->run($path, $pretend);
                foreach ($this->migrator->getNotes() as $note) {
                    $this->comment($note);
                }
                if ($this->input->getOption('seed')) {
                    $this->call('module:seed ' . $module->getName(), ['--force' => true]);
                }
//            $this->call('migrate', $this->getParameter($module));
                $this->info("Module {$module->getName()} migrated !");
            }
            return $this->info("All modules migrated !");

        }
//        foreach ($this->module->all() as $name) {
//            $this->dbSeed($name);
//        }
//        return $this->info("All modules seeded.");
    }

    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->input->getOption('database'));

        if (!$this->migrator->repositoryExists()) {
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

    protected function getOptions()
    {
        return array(
            array('bench', null, InputOption::VALUE_OPTIONAL, 'The name of the workbench to migrate . ', null),
            array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
            array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production . '),
            array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files . ', null),
            array('package', null, InputOption::VALUE_OPTIONAL, 'The package to migrate . ', null),
            array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run . '),
            array(
                'seed',
                null,
                InputOption::VALUE_NONE,
                'Indicates if the {
                        seed} task should be re - run . '
            ),
        );
    }
}
