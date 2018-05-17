<?php
/**
 * Created by PhpStorm.
 * User: totoro
 * Date: 2018-05-15
 * Time: 22:27
 */

namespace Totoro\Apollo;


use Illuminate\Support\ServiceProvider;
use Totoro\Apollo\Commands\ClearApolloCommand;
use Totoro\Apollo\Commands\PublishComponentCommand;
use Totoro\Apollo\Commands\PublishCommand;
use Totoro\Apollo\Commands\PublishConfigCommand;
use Totoro\Apollo\Commands\PublishConsulCommand;

class ApolloServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
//    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/apollo.php' => base_path('config/apollo.php')
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('apollo', function ($app){
            return new ApolloManage($app);
        });
        $this->app->singleton('command.apollo.publish', function (){
            return new PublishCommand();
        });
        $this->app->singleton('command.apollo.publish-config', function (){
            return new PublishConfigCommand();
        });
        $this->app->singleton('command.apollo.publish-consul', function (){
            return new PublishConsulCommand();
        });
        $this->app->singleton('command.apollo.publish-component', function (){
            return new PublishComponentCommand();
        });
        $this->app->singleton('command.apollo.clear-apollo', function (){
            return new ClearApolloCommand();
        });
        $this->commands(
            'command.apollo.publish',
            'command.apollo.publish-config',
            'command.apollo.publish-consul',
            'command.apollo.publish-component',
            'command.apollo.clear-apollo'
        );
    }
}