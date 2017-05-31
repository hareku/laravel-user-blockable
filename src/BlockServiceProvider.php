<?php

namespace Hareku\LaravelBlockable;

use Illuminate\Support\ServiceProvider;

class BlockableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/blockable.php' => config_path('blockable.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/blockable.php', 'blockable'
        );
    }
}
