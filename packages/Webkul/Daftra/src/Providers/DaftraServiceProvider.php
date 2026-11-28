<?php

namespace Webkul\Daftra\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Daftra\Console\Commands\SyncDaftraQuantities;
use Webkul\Daftra\Console\Commands\SyncMissingDaftraOrders;
use Webkul\Daftra\Http\Controllers\DaftraController;
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

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'daftra');

        Event::listen('checkout.order.save.after', function ($order) {
            if (empty(config('daftra.api_key')) && empty(config('daftra.client_secret'))) {
                return;
            }

            $orderedSkus = [];
            if (method_exists($order, 'loadMissing')) {
                $order->loadMissing('items.child');
            }

            foreach ($order->items ?? [] as $item) {
                $sku = trim((string) ($item->child?->sku ?: $item->sku));
                if ($sku !== '') {
                    $orderedSkus[] = $sku;
                }
            }

            if (! empty($orderedSkus)) {
                app(DaftraController::class)->forgetLiveStockCache($orderedSkus);
            }

            SyncOrderToDaftraJob::dispatch($order->id)->afterCommit();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncDaftraQuantities::class,
                SyncMissingDaftraOrders::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('daftra:sync-quantities')
                    ->everyFiveMinutes()
                    ->withoutOverlapping(10);
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
