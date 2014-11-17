<?php namespace Cms\Console\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedCommand extends Command
{

    protected $name = 'module:seed';

    protected $description = 'Seed a module';

    public function fire()
    {
        $this->module = $this->laravel['modules'];
        $module = Str::studly($this->argument('module'));
        if ($module) {
//            if ($this->module->has($module)) {
                $this->dbseed($module);
                return $this->info("Module [$module] seeded.");
//            }
            return $this->error("Module [$module] does not exists.");
        }
        foreach ($this->module->all() as $name) {
            $this->dbseed($name);
        }
        return $this->info("All modules seeded.");
    }

    /**
     * Seed the specified module.
     *
     * @parama string  $name
     * @return array
     */
    protected function dbseed($name)
    {
        $params = [
            '--class' => $this->option('class') ?: $this->getSeederName($name)
        ];
        if ($option = $this->option('database')) {
            $params['--database'] = $option;
        }
        $this->call('db:seed', $params);
    }

    /**
     * Get master database seeder name for the specified module.
     *
     * @param  string $name
     * @return string
     */
    public function getSeederName($name)
    {
        $name = Str::studly($name);
        return 'Modules\\' . $name . '\Database\Seeders\\' . $name . 'DatabaseSeeder';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('module', InputArgument::OPTIONAL, 'The name of module will be used.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', null),
            array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed.'),
        );
    }
}
