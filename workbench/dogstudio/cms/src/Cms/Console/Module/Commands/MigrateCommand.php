<?php namespace Cms\Console\Module\Commands;

use Cms\Console\Module\BaseModuleCommandTrait;
use Cms\Console\Module\Traits\BaseModuleCommandTraits;
use Cms\Modules\Module;
use Cms\Modules\Traits\ModulesTrait;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateCommand extends Command
{
    private $migrator;

    private $module;

    protected $name = 'module:migrate';

    protected $description = 'Migrate module(s)';

    public function __construct($app, Migrator $migrator, $module)
    {
        parent::__construct(new Str);
        $this->migrator = $migrator;
        $this->app = $app;
        $this->modules = $module;
    }

    use BaseModuleCommandTraits;
    use ModulesTrait;

    public function fire($moduleArgument = null)
    {
        $this->setModuleInArgument($moduleArgument);
        $module = $this->getModuleInArgument();
        if ($module) {
            return $this->migrate($module);
        } else {
            foreach ($this->getModules()->getEnabled() as $module) {
                $pretend = $this->input->getOption('pretend');
                $path = $module->getMigrationPath();
                $this->comment("Migrating module: {$module->getName()} ...");
                $this->migrator->run($path, $pretend);
                foreach ($this->migrator->getNotes() as $note) {
                    $this->comment($note);
                }
                if ($this->input->getOption('seed')) {
                    $this->seedModuleCommand();
                }
                $this->info("Module {$module->getName()} migrated !");
            }
            return $this->info("All modules migrated !");

        }
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
        if ($option = $this->option('force')) {
            $params['--force'] = $option;
        }
        return $params;
    }

    protected function getOptions()
    {
        return array(
            array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
            array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production . '),
            array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files . ', null),
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

    /**
     * @param $module
     */
    private function migrate($module)
    {
        $path = $this->module->getMigrationPath();
        $this->comment("Migrating module: {$module->getName()} ...");

        //We use the laravel migrator class to run migration
        $this->migrator->run($path);
        $this->writeMigrationInfos();
        $this->seedIfInOption();

        return $this->info("Module $module migrated !");
    }

    private function writeMigrationInfos()
    {
        foreach ($this->migrator->getNotes() as $note) {
            $this->comment($note);
        }
    }

    private function seedIfInOption()
    {
        if ($this->input->getOption('seed')) {
            $this->seedModuleCommand();
        }
    }

}
