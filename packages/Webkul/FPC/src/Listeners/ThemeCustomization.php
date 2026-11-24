<?php

namespace Webkul\FPC\Listeners;

use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\FPC\Hasher\DefaultHasher;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ThemeCustomization
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected ThemeCustomizationRepository $themeCustomizationRepository) {}

    /**
     * After theme customization create.
     *
     * @param  \Webkul\Theme\Contracts\ThemeCustomization|null  $themeCustomization
     * @return void
     */
    public function afterCreate($themeCustomization)
    {
        $this->invalidateCache($themeCustomization?->type);
    }

    /**
     * After theme customization update.
     *
     * @param  \Webkul\Theme\Contracts\ThemeCustomization|null  $themeCustomization
     * @return void
     */
    public function afterUpdate($themeCustomization)
    {
        $this->invalidateCache($themeCustomization?->type);
    }

    /**
     * Before theme customization delete.
     *
     * @param  int  $themeCustomizationId
     * @return void
     */
    public function beforeDelete($themeCustomizationId)
    {
        $themeCustomization = $this->themeCustomizationRepository->find($themeCustomizationId);

        if (! $themeCustomization) {
            return;
        }

        $this->invalidateCache($themeCustomization->type);
    }

    /**
     * Invalidate full page cache affected by theme customization changes.
     */
    public function invalidateCache(?string $type = null): void
    {
        if (! config('responsecache.enabled')) {
            return;
        }

        if (in_array($type, ['footer_links', 'services_content'])) {
            ResponseCache::clear();

            return;
        }

        $oldVersion = DefaultHasher::getHomeCacheVersion();

        $this->forgetHomePageUrls($oldVersion);

        DefaultHasher::bumpHomeCacheVersion();
    }

    /**
     * Proactively delete cached guest homepage entries for all channels, locales, and currencies.
     */
    protected function forgetHomePageUrls(string $version): void
    {
        $rawAppUrl = rtrim((string) config('app.url'), '/');
        $appUrl = $rawAppUrl !== '' ? $rawAppUrl.'/' : null;
        $scheme = ($appUrl ? parse_url($appUrl, PHP_URL_SCHEME) : null) ?: 'https';

        foreach (core()->getAllChannels() ?? [] as $channel) {
            $channelUrls = $appUrl ? [$appUrl] : [];

            if (! empty($channel->hostname)) {
                $hostname = rtrim(trim((string) $channel->hostname), '/');

                if ($hostname !== '') {
                    if (! preg_match('#^https?://#i', $hostname)) {
                        $hostname = $scheme.'://'.$hostname;
                    }

                    $channelUrls[] = $hostname.'/';
                }
            }

            $channelUrls = array_values(array_unique(array_filter($channelUrls))) ?: ['/'];

            foreach ($channel->locales ?? [] as $locale) {
                foreach ($channel->currencies ?? [] as $currency) {
                    $suffix = $channel->code.'-'.$locale->code.'-'.$currency->code.'--hv'.$version;

                    ResponseCache::selectCachedItems()
                        ->forUrls($channelUrls)
                        ->usingSuffix($suffix)
                        ->forget();
                }
            }
        }
    }
}
