<?php

namespace Webkul\Daftra\Providers;

use Illuminate\Support\ServiceProvider;

class DaftraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__ . '/../Http/routes.php';

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'daftra');

        \Illuminate\Support\Facades\Event::listen('checkout.order.save.after', function ($order) {
            app(\Webkul\Daftra\Listeners\OrderListener::class)->syncOrderToDaftra($order);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\Daftra\Console\Commands\SyncDaftraQuantities::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
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
            dirname(__DIR__) . '/Config/daftra.php',
            'daftra'
        );
    }
}
