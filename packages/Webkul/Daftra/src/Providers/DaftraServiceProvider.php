<?php

namespace Webkul\Daftra\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Daftra\Console\Commands\SyncDaftraQuantities;
use Webkul\Daftra\Jobs\SyncOrderToDaftraJob;

class DaftraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/../Http/routes.php';

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'daftra');

        Event::listen('checkout.order.save.after', function ($order) {
            if (empty(config('daftra.api_key')) && empty(config('daftra.client_secret'))) {
                return;
            }

            SyncOrderToDaftraJob::dispatch($order->id)->afterCommit();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncDaftraQuantities::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('daftra:sync-quantities')->hourly();
            });
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/daftra.php',
            'daftra'
        );
    }
}
