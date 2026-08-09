<?php

namespace Webkul\FPC\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Models\ProductVideo;
use Webkul\FPC\Listeners\Product as ProductListener;

class FPCServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->register(EventServiceProvider::class);

        ProductImage::saved(function ($image) {
            if ($image->product) {
                app(ProductListener::class)->afterUpdate($image->product);
            }
        });

        ProductImage::deleted(function ($image) {
            if ($image->product) {
                app(ProductListener::class)->afterUpdate($image->product);
            }
        });

        ProductVideo::saved(function ($video) {
            if ($video->product) {
                app(ProductListener::class)->afterUpdate($video->product);
            }
        });

        ProductVideo::deleted(function ($video) {
            if ($video->product) {
                app(ProductListener::class)->afterUpdate($video->product);
            }
        });
    }
}
