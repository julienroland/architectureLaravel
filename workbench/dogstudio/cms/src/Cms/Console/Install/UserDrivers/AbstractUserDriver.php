<?php  namespace Cms\Console\Install\UserDrivers;

abstract class AbstractUserDriver
{
    private $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    abstract public function generate();

}
