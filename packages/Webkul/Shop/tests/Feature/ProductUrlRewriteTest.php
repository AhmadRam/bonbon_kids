<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Marketing\Models\URLRewrite;
use Webkul\Product\Repositories\ProductRepository;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

it('renders an active product even when a stale url rewrite claims its url_key', function () {
    $activeProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    $inactiveProduct = (new ProductFaker)->getSimpleProductFactory()->create();
    app(ProductRepository::class)->update(['status' => 0], $inactiveProduct->id, ['status']);

    $anotherActiveProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    // Case 1: stale rewrite points from active product's url_key to an inactive product.
    URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => $activeProduct->url_key,
        'target_path' => $inactiveProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    get('/'.$activeProduct->url_key)
        ->assertOk()
        ->assertSeeText($activeProduct->name);

    // Case 2: stale rewrite points from active product's url_key to another active product.
    URLRewrite::where('request_path', $activeProduct->url_key)->update([
        'target_path' => $anotherActiveProduct->url_key,
    ]);

    get('/'.$activeProduct->url_key)
        ->assertOk()
        ->assertSeeText($activeProduct->name);
});

it('returns 404 instead of 301 when a url rewrite points to an inactive product', function () {
    $inactiveProduct = (new ProductFaker)->getSimpleProductFactory()->create();
    app(ProductRepository::class)->update(['status' => 0], $inactiveProduct->id, ['status']);

    URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => 'legacy-inactive-product-slug',
        'target_path' => $inactiveProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    get('/legacy-inactive-product-slug')
        ->assertNotFound();
});

it('redirects 301 when a valid url rewrite points to an active visible product', function () {
    $activeProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => 'legacy-valid-product-slug',
        'target_path' => $activeProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    get('/legacy-valid-product-slug')
        ->assertRedirect('/'.$activeProduct->url_key)
        ->assertStatus(301);
});

it('cleans conflicting and dead url rewrites through product lifecycle events', function () {
    $product = (new ProductFaker)->getSimpleProductFactory()->create();
    $oldUrlKey = $product->url_key;
    $newUrlKey = $oldUrlKey.'-updated';

    // Seed a conflicting rewrite claiming the new url_key.
    URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => $newUrlKey,
        'target_path' => 'some-other-stale-slug',
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    // Simulate product update changing url_key from $oldUrlKey to $newUrlKey.
    request()->merge([
        'locale' => app()->getLocale(),
        'url_key' => $newUrlKey,
        'status' => 1,
    ]);

    Event::dispatch('catalog.product.update.before', $product->id);

    $updatedProduct = app(ProductRepository::class)->update([
        'url_key' => $newUrlKey,
        'locale' => app()->getLocale(),
        'channel' => core()->getDefaultChannelCode(),
    ], $product->id, ['url_key']);

    Event::dispatch('catalog.product.update.after', $updatedProduct);

    // The conflicting rewrite claiming $newUrlKey must be deleted, while old -> new is created.
    $this->assertDatabaseMissing('url_rewrites', [
        'request_path' => $newUrlKey,
        'target_path' => 'some-other-stale-slug',
    ]);

    $this->assertDatabaseHas('url_rewrites', [
        'entity_type' => 'product',
        'request_path' => $oldUrlKey,
        'target_path' => $newUrlKey,
    ]);

    // Disabling the product must remove rewrites targeting its url_key.
    request()->replace([]);
    Event::dispatch('catalog.product.update.before', $updatedProduct->id);
    $disabledProduct = app(ProductRepository::class)->update(['status' => 0], $updatedProduct->id, ['status']);
    Event::dispatch('catalog.product.update.after', $disabledProduct);

    $this->assertDatabaseMissing('url_rewrites', [
        'entity_type' => 'product',
        'target_path' => $newUrlKey,
    ]);

    // Re-create a rewrite and verify deletion removes both request_path and target_path rewrites.
    $freshProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => 'old-slug-to-deleted',
        'target_path' => $freshProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    Event::dispatch('catalog.product.delete.before', $freshProduct->id);

    $this->assertDatabaseMissing('url_rewrites', [
        'entity_type' => 'product',
        'target_path' => $freshProduct->url_key,
    ]);
});

it('excludes inactive products from related and up-sell API listings', function () {
    $mainProduct = (new ProductFaker)->getSimpleProductFactory()->create();
    $activeRelated = (new ProductFaker)->getSimpleProductFactory()->create();
    $inactiveRelated = (new ProductFaker)->getSimpleProductFactory()->create();
    app(ProductRepository::class)->update(['status' => 0], $inactiveRelated->id, ['status']);

    $mainProduct->related_products()->sync([$activeRelated->id, $inactiveRelated->id]);
    $mainProduct->up_sells()->sync([$activeRelated->id, $inactiveRelated->id]);

    getJson(route('shop.api.products.related.index', $mainProduct->id))
        ->assertOk()
        ->assertJsonFragment(['id' => $activeRelated->id])
        ->assertJsonMissing(['id' => $inactiveRelated->id]);

    getJson(route('shop.api.products.up-sell.index', $mainProduct->id))
        ->assertOk()
        ->assertJsonFragment(['id' => $activeRelated->id])
        ->assertJsonMissing(['id' => $inactiveRelated->id]);
});

it('cleans invalid url rewrites via cleanup command while preserving valid ones', function () {
    $activeProduct = (new ProductFaker)->getSimpleProductFactory()->create();
    $inactiveProduct = (new ProductFaker)->getSimpleProductFactory()->create();
    app(ProductRepository::class)->update(['status' => 0], $inactiveProduct->id, ['status']);

    // Colliding rewrite claiming active product's live url_key.
    $colliding = URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => $activeProduct->url_key,
        'target_path' => $inactiveProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    // Dead rewrite targeting an inactive product.
    $dead = URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => 'dead-old-slug',
        'target_path' => $inactiveProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    // Valid rewrite targeting an active product from an unused slug.
    $valid = URLRewrite::create([
        'entity_type' => 'product',
        'request_path' => 'valid-old-slug',
        'target_path' => $activeProduct->url_key,
        'redirect_type' => 301,
        'locale' => app()->getLocale(),
    ]);

    Artisan::call('bagisto:url-rewrites:cleanup');

    $this->assertDatabaseMissing('url_rewrites', ['id' => $colliding->id]);
    $this->assertDatabaseMissing('url_rewrites', ['id' => $dead->id]);
    $this->assertDatabaseHas('url_rewrites', ['id' => $valid->id]);
});
