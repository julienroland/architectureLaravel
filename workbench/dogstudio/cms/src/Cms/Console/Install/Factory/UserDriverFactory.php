<?php  namespace Cms\Console\Install\Factory;

class UserDriverFactory
{
    public function handle($that, $app, $file, $driver)
    {
        $class = "Cms\\Console\\Install\\UserDrivers\\" . ucfirst($driver) . "UserDriverCommand";
        $driver = new $class($that, $app, $file);
        return $driver->generate();
    }
}
