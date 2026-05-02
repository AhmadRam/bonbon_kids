<?php

namespace Webkul\UPayments\Providers;

use Illuminate\Support\ServiceProvider;

class UPaymentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        include __DIR__.'/../Http/routes.php';

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'upayments');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/paymentmethods.php',
            'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php',
            'core'
        );
    }
}
