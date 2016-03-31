<?php

namespace Zandervdm\Gitception;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class GitceptionServiceProvider extends ServiceProvider
{

    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/gitception.php' => config_path('gitception.php')
        ]);

        $loader = AliasLoader::getInstance();
        $loader->alias('Gitception', GitceptionFacade::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        config([
            'config/gitception.php',
        ]);

        $this->app['zandervdm.gitception'] = $this->app->share(function($app){
            return new GitceptionClass();
        });
    }
}
