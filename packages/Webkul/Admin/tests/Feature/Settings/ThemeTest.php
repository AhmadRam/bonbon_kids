<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponseCache\Facades\ResponseCache;
use Spatie\ResponseCache\ResponseCache as BaseResponseCache;
use Spatie\ResponseCache\ResponseCacheRepository;
use Webkul\Core\Models\Locale;
use Webkul\Customer\Models\Customer;
use Webkul\FPC\Hasher\DefaultHasher;
use Webkul\Theme\Models\ThemeCustomization;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

it('should returns the theme index page', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    get(route('admin.settings.themes.index'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.settings.themes.index.title'))
        ->assertSeeText(trans('admin::app.settings.themes.index.create-btn'));
});

it('should fail the validation with errors when certain field not provided when store the theme', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should fail the validation with errors when correct type not provided when store the theme', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'), [
        'type' => 'INVALID_TYPE',
    ])
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should store the newly created theme', function () {
    // Arrange.
    $lastThemeId = ThemeCustomization::factory()->create()->id + 1;

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'), [
        'type' => $type = fake()->randomElement([
            'product_carousel',
            'category_carousel',
            'image_carousel',
            'footer_links',
            'services_content',
        ]),
        'name' => $name = fake()->name(),
        'sort_order' => $lastThemeId,
        'channel_id' => $channelId = core()->getCurrentChannel()->id,
        'theme_code' => $themeCode = core()->getCurrentChannel()->theme,
    ])
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.settings.themes.edit', $lastThemeId));

    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id' => $lastThemeId,
                'type' => $type,
                'name' => $name,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
            ],
        ],
    ]);
});

it('should fail the validation with errors when correct type not provided when update the theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should update the theme customizations', function () {
    $theme = ThemeCustomization::factory()->create();

    $data = [];

    switch ($theme->type) {
        case ThemeCustomization::PRODUCT_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    'title' => fake()->title(),
                    'filters' => [
                        'sort' => 'name-desc',
                        'limit' => '12',
                        'new' => '1',
                    ],
                ],
            ];

            break;

        case ThemeCustomization::CATEGORY_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    'title' => fake()->title(),
                    'filters' => [
                        'sort' => 'desc',
                        'limit' => '10',
                        'parent_id' => '1',
                    ],
                ],
            ];

            break;

        case ThemeCustomization::IMAGE_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    [
                        'title' => fake()->title(),
                        'link' => fake()->url(),
                        'image' => UploadedFile::fake()->image(fake()->word().'.png', 640, 480, 'png'),
                    ],
                ],
            ];

            break;

        case ThemeCustomization::FOOTER_LINKS:
            $data[app()->getLocale()] = [
                'options' => [
                    'column_1' => [
                        [
                            'url' => fake()->url(),
                            'title' => fake()->title(),
                            'sort_order' => '1',
                        ],
                    ],
                ],
            ];

            break;

        case ThemeCustomization::SERVICES_CONTENT:
            $data[app()->getLocale()] = [
                'options' => [
                    [
                        'title' => fake()->title(),
                        'description' => fake()->paragraph(),
                        'service_icon' => 'icon-truck',
                    ],
                ],
            ];

            break;
    }

    $data['locale'] = app()->getLocale();
    $data['type'] = $theme->type;
    $data['name'] = $name = fake()->name();
    $data['sort_order'] = '1';
    $data['channel_id'] = core()->getCurrentChannel()->id;
    $data['theme_code'] = core()->getCurrentChannel()->theme;
    $data['status'] = 'on';

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id' => $theme->id,
                'type' => $theme->type,
                'name' => $name,
            ],
        ],
    ]);
});

it('should sanitize malicious script tags from static content HTML when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $maliciousHtml = '<div>Safe content</div><script>alert("XSS")</script><p>More safe content</p>';

    $safeCss = 'body { color: red; }';

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $maliciousHtml,
                'css' => $safeCss,
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => $name = fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that script tag was removed.
    expect($translation->options['html'])->not->toContain('<script>');
    expect($translation->options['html'])->not->toContain('alert("XSS")');
    expect($translation->options['html'])->toContain('Safe content');
    expect($translation->options['html'])->toContain('More safe content');
});

it('should sanitize iframe tags from static content HTML when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $maliciousHtml = '<div>Content</div><iframe src="https://malicious.com"></iframe><p>More content</p>';

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $maliciousHtml,
                'css' => '',
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that iframe tag was removed.
    expect($translation->options['html'])->not->toContain('<iframe');
    expect($translation->options['html'])->not->toContain('malicious.com');
    expect($translation->options['html'])->toContain('Content');
    expect($translation->options['html'])->toContain('More content');
});

it('should sanitize form tags from static content HTML when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $maliciousHtml = '<div>Safe content</div><form action="/submit" method="post"><input name="data"></form><p>More content</p>';

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $maliciousHtml,
                'css' => '',
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that form tag was removed.
    expect($translation->options['html'])->not->toContain('<form');
    expect($translation->options['html'])->not->toContain('</form>');
    expect($translation->options['html'])->toContain('Safe content');
    expect($translation->options['html'])->toContain('More content');
});

it('should preserve safe HTML content in static content when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $safeHtml = '<div class="container"><h1>Title</h1><p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p><ul><li>Item 1</li><li>Item 2</li></ul></div>';

    $safeCss = 'body { color: blue; font-size: 14px; }';

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $safeHtml,
                'css' => $safeCss,
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that safe HTML elements are preserved.
    expect($translation->options['html'])->toContain('<div');
    expect($translation->options['html'])->toContain('<h1>');
    expect($translation->options['html'])->toContain('<p>');
    expect($translation->options['html'])->toContain('<strong>');
    expect($translation->options['html'])->toContain('<em>');
    expect($translation->options['html'])->toContain('<ul>');
    expect($translation->options['html'])->toContain('<li>');
});

it('should sanitize malicious event handlers from static content HTML when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $maliciousHtml = '<div onclick="alert(\'XSS\')">Click me</div><img src="x" onerror="alert(\'XSS\')">';

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $maliciousHtml,
                'css' => '',
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that malicious event handlers were removed.
    expect($translation->options['html'])->not->toContain('onclick');
    expect($translation->options['html'])->not->toContain('onerror');
    expect($translation->options['html'])->not->toContain('alert(');
    expect($translation->options['html'])->toContain('Click me');
});

it('should not sanitize HTML for non-static content theme types', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'product_carousel',
    ]);

    $data = [
        app()->getLocale() => [
            'options' => [
                'title' => 'Test Title',
                'filters' => [
                    'sort' => 'name-desc',
                    'limit' => '12',
                    'new' => '1',
                ],
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'product_carousel',
        'name' => $name = fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();

    // Assert theme was updated successfully.
    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id' => $theme->id,
                'type' => 'product_carousel',
                'name' => $name,
            ],
        ],
    ]);
});

it('should delete the theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    deleteJson(route('admin.settings.themes.delete', $theme->id))
        ->assertOk()
        ->assertJsonPath('message', trans('admin::app.settings.themes.delete-success'));

    $this->assertDatabaseMissing('theme_customizations', [
        'id' => $theme->id,
    ]);

    $this->assertDatabaseMissing('theme_customization_translations', [
        'theme_customization_id' => $theme->id,
    ]);
});

it('invalidates homepage response cache across locales, trailing-slash APP_URL, and logged-in customers when updating an image carousel', function () {
    Storage::fake(config('filesystems.default'));

    config([
        'responsecache.enabled' => true,
        'responsecache.cache_store' => 'array',
        'responsecache.add_cache_time_header' => true,
        'app.url' => 'https://bonbonkw.com/',
    ]);

    app()->forgetInstance('responsecache');
    app()->forgetInstance(BaseResponseCache::class);
    app()->forgetInstance(ResponseCacheRepository::class);
    ResponseCache::clearResolvedInstance('responsecache');

    $channel = core()->getCurrentChannel();
    $arLocale = Locale::firstOrCreate(
        ['code' => 'ar'],
        ['name' => 'Arabic', 'direction' => 'rtl']
    );
    $channel->locales()->syncWithoutDetaching([$arLocale->id]);
    $channel->refresh()->load(['locales', 'currencies', 'default_locale']);
    core()->setCurrentChannel($channel);

    $theme = ThemeCustomization::factory()->create([
        'type' => ThemeCustomization::IMAGE_CAROUSEL,
        'status' => 1,
        'channel_id' => $channel->id,
        'theme_code' => $channel->theme,
        'ar' => [
            'options' => [
                'images' => [
                    [
                        'title' => 'Old AR Slide',
                        'link' => '/products',
                        'image' => 'storage/theme/test/old-ar-banner.webp',
                    ],
                ],
            ],
        ],
        'en' => [
            'options' => [
                'images' => [
                    [
                        'title' => 'Old EN Slide',
                        'link' => '/products',
                        'image' => 'storage/theme/test/old-en-banner.webp',
                    ],
                ],
            ],
        ],
    ]);

    // Prime guest AR homepage cache and verify second hit is served from FPC.
    get(route('shop.home.index', ['locale' => 'ar']))
        ->assertOk()
        ->assertSee('old-ar-banner.webp');

    get(route('shop.home.index', ['locale' => 'ar']))
        ->assertOk()
        ->assertHeader('Bagisto-FPC')
        ->assertSee('old-ar-banner.webp');

    // Prime logged-in customer AR homepage cache.
    $customer = Customer::factory()->create();
    $this->actingAs($customer, 'customer');

    get(route('shop.home.index', ['locale' => 'ar']))
        ->assertOk()
        ->assertSee('old-ar-banner.webp');

    get(route('shop.home.index', ['locale' => 'ar']))
        ->assertOk()
        ->assertHeader('Bagisto-FPC')
        ->assertSee('old-ar-banner.webp');

    $versionBefore = DefaultHasher::getHomeCacheVersion();

    // Update AR slider while Admin UI is running in EN.
    $this->loginAsAdmin();
    app()->setLocale('en');

    $this->withSession(['admin_locale' => 'en'])
        ->postJson(route('admin.settings.themes.update', $theme->id), [
            'locale' => 'ar',
            'type' => ThemeCustomization::IMAGE_CAROUSEL,
            'name' => $theme->name,
            'sort_order' => (string) $theme->sort_order,
            'channel_id' => $channel->id,
            'theme_code' => $channel->theme,
            'status' => 'on',
            'ar' => [
                'deleted_sliders' => [
                    ['image' => 'storage/theme/test/old-ar-banner.webp'],
                ],
                'options' => [
                    [
                        'title' => 'New AR Slide',
                        'link' => '/products',
                        'image' => UploadedFile::fake()->image('new-ar-banner.png', 100, 100),
                    ],
                ],
            ],
        ])
        ->assertRedirect(route('admin.settings.themes.index'));

    $theme->refresh();
    $newImageFilename = basename($theme->translate('ar')->options['images'][0]['image']);

    expect(DefaultHasher::getHomeCacheVersion())->not->toBe($versionBefore);

    // Verify logged-in customer immediately receives the new AR banner.
    get(route('shop.home.index', ['locale' => 'ar']))
        ->assertOk()
        ->assertSee($newImageFilename)
        ->assertDontSee('old-ar-banner.webp');

    // Verify guest immediately receives the new AR banner.
    auth()->guard('customer')->logout();

    get(route('shop.home.index', ['locale' => 'ar']))
        ->assertOk()
        ->assertSee($newImageFilename)
        ->assertDontSee('old-ar-banner.webp');
});

it('defaults theme edit page to the channel storefront default locale when locale query is omitted', function () {
    $channel = core()->getCurrentChannel();
    $originalDefaultLocaleId = $channel->default_locale_id;
    $arLocale = Locale::firstOrCreate(
        ['code' => 'ar'],
        ['name' => 'Arabic', 'direction' => 'rtl']
    );
    $channel->locales()->syncWithoutDetaching([$arLocale->id]);
    $channel->update(['default_locale_id' => $arLocale->id]);
    $channel->refresh()->load(['locales', 'currencies', 'default_locale']);
    core()->setCurrentChannel($channel);

    $theme = ThemeCustomization::factory()->create([
        'type' => ThemeCustomization::IMAGE_CAROUSEL,
        'channel_id' => $channel->id,
        'theme_code' => $channel->theme,
        'ar' => [
            'options' => [
                'images' => [
                    [
                        'title' => 'AR Banner',
                        'link' => '/ar',
                        'image' => 'storage/theme/test/ar-only-banner.webp',
                    ],
                ],
            ],
        ],
        'en' => [
            'options' => [
                'images' => [
                    [
                        'title' => 'EN Banner',
                        'link' => '/en',
                        'image' => 'storage/theme/test/en-only-banner.webp',
                    ],
                ],
            ],
        ],
    ]);

    $this->loginAsAdmin();
    app()->setLocale('en');

    try {
        $defaultResponse = $this->withSession(['admin_locale' => 'en'])
            ->get(route('admin.settings.themes.edit', $theme->id))
            ->assertOk()
            ->assertSee('ar-only-banner.webp');

        expect($defaultResponse->getContent())->toMatch('/name="locale"\s+value="ar"/');

        $enResponse = $this->withSession(['admin_locale' => 'en'])
            ->get(route('admin.settings.themes.edit', ['id' => $theme->id, 'locale' => 'en']))
            ->assertOk()
            ->assertSee('en-only-banner.webp');

        expect($enResponse->getContent())->toMatch('/name="locale"\s+value="en"/');
    } finally {
        $channel->update(['default_locale_id' => $originalDefaultLocaleId]);
        core()->setCurrentChannel($channel->fresh(['locales', 'currencies', 'default_locale']));
    }
});

it('creates missing locale translations, clears emptied sliders, and keeps images still referenced by another locale', function () {
    $disk = config('filesystems.default');
    Storage::fake($disk);

    Storage::disk($disk)->put('theme/shared/banner.webp', 'shared-binary');

    $channel = core()->getCurrentChannel();
    $arLocale = Locale::firstOrCreate(
        ['code' => 'ar'],
        ['name' => 'Arabic', 'direction' => 'rtl']
    );
    $channel->locales()->syncWithoutDetaching([$arLocale->id]);

    $theme = ThemeCustomization::factory()->create([
        'type' => ThemeCustomization::IMAGE_CAROUSEL,
        'channel_id' => $channel->id,
        'theme_code' => $channel->theme,
        'en' => [
            'options' => [
                'images' => [
                    [
                        'title' => 'Shared Banner',
                        'link' => '/products',
                        'image' => 'storage/theme/shared/banner.webp',
                    ],
                ],
            ],
        ],
    ]);

    expect($theme->translate('ar'))->toBeNull();

    $repository = app(ThemeCustomizationRepository::class);

    // Saving AR for the first time creates the translation and preserves the shared image still used by EN.
    request()->merge(['locale' => 'ar']);

    $repository->uploadImage([
        'locale' => 'ar',
        'type' => ThemeCustomization::IMAGE_CAROUSEL,
        'ar' => [
            'deleted_sliders' => [
                ['image' => 'storage/theme/shared/banner.webp'],
            ],
            'options' => [
                [
                    'title' => 'AR Brand New',
                    'link' => '/ar',
                    'image' => UploadedFile::fake()->image('ar-new.png', 80, 80),
                ],
            ],
        ],
    ], $theme);

    $theme->refresh();
    $arTranslation = $theme->translate('ar');

    expect($arTranslation)->not->toBeNull();
    expect($arTranslation->options['images'] ?? [])->toHaveCount(1);
    expect(Storage::disk($disk)->exists('theme/shared/banner.webp'))->toBeTrue();

    $arStoredImage = $arTranslation->options['images'][0]['image'];
    $arRelativePath = preg_replace('#^storage/#', '', $arStoredImage);
    expect(Storage::disk($disk)->exists($arRelativePath))->toBeTrue();

    // Removing all sliders in AR saves empty options and deletes the AR-only file.
    $repository->uploadImage([
        'locale' => 'ar',
        'type' => ThemeCustomization::IMAGE_CAROUSEL,
        'ar' => [
            'deleted_sliders' => [
                ['image' => $arStoredImage],
            ],
        ],
    ], $theme);

    $theme->refresh();

    expect($theme->translate('ar')->options)->toBe([]);
    expect(Storage::disk($disk)->exists($arRelativePath))->toBeFalse();
});

it('invalidates homepage response cache on massUpdate and massDestroy', function () {
    config([
        'responsecache.enabled' => true,
        'responsecache.cache_store' => 'array',
    ]);

    app()->forgetInstance('responsecache');
    app()->forgetInstance(BaseResponseCache::class);
    app()->forgetInstance(ResponseCacheRepository::class);
    ResponseCache::clearResolvedInstance('responsecache');

    $theme = ThemeCustomization::factory()->create([
        'type' => ThemeCustomization::IMAGE_CAROUSEL,
        'status' => 1,
    ]);

    $this->loginAsAdmin();

    $versionBeforeMassUpdate = DefaultHasher::getHomeCacheVersion();

    postJson(route('admin.settings.themes.mass_update'), [
        'indices' => [$theme->id],
        'value' => 0,
    ])->assertOk();

    $versionAfterMassUpdate = DefaultHasher::getHomeCacheVersion();

    expect($versionAfterMassUpdate)->not->toBe($versionBeforeMassUpdate);

    postJson(route('admin.settings.themes.mass_delete'), [
        'indices' => [$theme->id],
    ])->assertOk();

    expect(DefaultHasher::getHomeCacheVersion())->not->toBe($versionAfterMassUpdate);
});
