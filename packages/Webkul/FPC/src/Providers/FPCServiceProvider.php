<?php

namespace Webkul\FPC\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\FPC\Listeners\Product as ProductListener;
use Webkul\FPC\Listeners\ThemeCustomization as ThemeCustomizationListener;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Models\ProductVideo;
use Webkul\Theme\Models\ThemeCustomization;
use Webkul\Theme\Models\ThemeCustomizationTranslation;

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

        ThemeCustomization::saved(function ($theme) {
            app(ThemeCustomizationListener::class)->invalidateCache($theme->type);
        });

        ThemeCustomization::deleted(function ($theme) {
            app(ThemeCustomizationListener::class)->invalidateCache($theme->type);
        });

        ThemeCustomizationTranslation::saved(function ($translation) {
            $type = ThemeCustomization::whereKey($translation->theme_customization_id)->value('type');

            app(ThemeCustomizationListener::class)->invalidateCache($type);
        });

        ThemeCustomizationTranslation::deleted(function ($translation) {
            $type = ThemeCustomization::whereKey($translation->theme_customization_id)->value('type');

            app(ThemeCustomizationListener::class)->invalidateCache($type);
        });
    }
}
