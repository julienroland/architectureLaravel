<?php  namespace Cms\Facades;

use Illuminate\Support\Facades\Facade;

class Module extends Facade
{
    public $alias = 'Module';

    protected static function getFacadeAccessor()
    {
        return 'modules';
    }

}
