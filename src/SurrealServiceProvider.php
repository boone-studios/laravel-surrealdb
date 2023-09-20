<?php

namespace BooneStudios\Surreal;

use BooneStudios\Surreal\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class SurrealServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Add the database driver
        $this->app->resolving('db', function ($db) {
            $db->extend('surrealdb', function ($config, $name) {
                $config['name'] = $name;

                return new Connection($config);
            });
        });
    }
}
