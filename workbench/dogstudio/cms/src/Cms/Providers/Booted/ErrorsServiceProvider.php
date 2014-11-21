<?php namespace Cms\Providers\Booted;

use Illuminate\Support\ServiceProvider;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorsServiceProvider extends ServiceProvider {

    /**
     * Register any error handlers.
     *
     * @return void
     */
    public function boot()
    {
        $whoops = new Run;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->register();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}