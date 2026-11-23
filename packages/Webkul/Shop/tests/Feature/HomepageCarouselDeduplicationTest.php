<?php

use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Theme\Models\ThemeCustomization;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

it('excludes requested product ids from the storefront products API', function () {
    $firstProduct = (new ProductFaker)->getSimpleProductFactory()->create();
    $secondProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    getJson(route('shop.api.products.index', [
        'exclude_ids' => (string) $firstProduct->id,
    ]))
        ->assertOk()
        ->assertJsonMissing(['id' => $firstProduct->id])
        ->assertJsonFragment(['id' => $secondProduct->id]);
});

it('passes exclude_ids to subsequent homepage product carousels while keeping view-all links unfiltered', function () {
    $featuredAndNewProduct = (new ProductFaker([
        'attributes' => [
            5 => 'new',
            6 => 'featured',
        ],
        'attribute_value' => [
            'new' => ['boolean_value' => true],
            'featured' => ['boolean_value' => true],
        ],
    ]))->getSimpleProductFactory()->create();

    $onlyNewProduct = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],
        'attribute_value' => [
            'new' => ['boolean_value' => true],
        ],
    ]))->getSimpleProductFactory()->create();

    $channel = core()->getCurrentChannel();
    $locale = app()->getLocale();

    ThemeCustomization::where('type', ThemeCustomization::PRODUCT_CAROUSEL)->delete();

    ThemeCustomization::create([
        'type' => ThemeCustomization::PRODUCT_CAROUSEL,
        'name' => 'Featured Test Carousel',
        'sort_order' => 1,
        'status' => 1,
        'channel_id' => $channel->id,
        'theme_code' => $channel->theme,
        $locale => [
            'options' => [
                'title' => 'Featured Carousel',
                'filters' => [
                    'featured' => 1,
                    'sort' => 'created_at-desc',
                    'limit' => 12,
                ],
            ],
        ],
    ]);

    ThemeCustomization::create([
        'type' => ThemeCustomization::PRODUCT_CAROUSEL,
        'name' => 'New Test Carousel',
        'sort_order' => 2,
        'status' => 1,
        'channel_id' => $channel->id,
        'theme_code' => $channel->theme,
        $locale => [
            'options' => [
                'title' => 'New Carousel',
                'filters' => [
                    'new' => 1,
                    'sort' => 'created_at-desc',
                    'limit' => 12,
                ],
            ],
        ],
    ]);

    $response = get(route('shop.home.index'))
        ->assertOk();

    $html = $response->getContent();

    // Extract carousel src and navigation-link URLs.
    preg_match_all('/<v-products-carousel[^>]*src="([^"]+)"[^>]*navigation-link="([^"]*)"/s', $html, $matches, PREG_SET_ORDER);

    expect($matches)->toHaveCount(2);

    $featuredSrc = html_entity_decode($matches[0][1]);
    $newSrc = html_entity_decode($matches[1][1]);
    $newNavLink = html_entity_decode($matches[1][2]);

    expect($featuredSrc)->not->toContain('exclude_ids');
    expect($newSrc)->toContain('exclude_ids=');
    expect($newSrc)->toContain((string) $featuredAndNewProduct->id);
    expect($newNavLink)->not->toContain('exclude_ids');

    // Verify the second carousel API endpoint returns only non-duplicate products.
    $parsedUrl = parse_url($newSrc);
    parse_str($parsedUrl['query'] ?? '', $queryParams);

    getJson(route('shop.api.products.index', $queryParams))
        ->assertOk()
        ->assertJsonMissing(['id' => $featuredAndNewProduct->id])
        ->assertJsonFragment(['id' => $onlyNewProduct->id]);
});
