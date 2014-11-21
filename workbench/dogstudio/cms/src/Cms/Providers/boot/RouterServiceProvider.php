<?php namespace Cms\Providers\Boot;

use Illuminate\Routing\Router;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;


class RouterServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        require 'router.php';
    }
}
