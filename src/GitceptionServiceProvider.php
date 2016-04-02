<?php namespace Zandervdm\Gitception;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class GitceptionServiceProvider extends ServiceProvider
{

    /**
     * boot function.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/gitception.php' => config_path('gitception.php'),
        ]);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'gitception');

        $loader = AliasLoader::getInstance();
        $loader->alias('Gitception', 'Zandervdm\Gitception\Facade');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        config([
            'config/gitception.php'
        ]);

        App::bind('zandervdm.gitception', function(){
            return new Gitception();
        });
    }

}