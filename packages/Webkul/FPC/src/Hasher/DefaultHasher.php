<?php

namespace Webkul\FPC\Hasher;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Hasher\DefaultHasher as BaseDefaultHasher;

class DefaultHasher extends BaseDefaultHasher
{
    /**
     * Cache key storing the active homepage FPC version token.
     */
    public const HOME_CACHE_VERSION_KEY = 'fpc_home_cache_version';

    /**
     * Get the current homepage cache version token.
     */
    public static function getHomeCacheVersion(): string
    {
        return (string) static::cacheStore()->get(self::HOME_CACHE_VERSION_KEY, '1');
    }

    /**
     * Rotate the homepage cache version token to invalidate all homepage cache variants.
     */
    public static function bumpHomeCacheVersion(): string
    {
        $version = (string) Str::uuid();

        static::cacheStore()->forever(self::HOME_CACHE_VERSION_KEY, $version);

        return $version;
    }

    /**
     * Resolve the cache store configured for response caching.
     */
    protected static function cacheStore(): CacheRepository
    {
        return Cache::store(config('responsecache.cache_store') ?: config('cache.default'));
    }

    /**
     * Determine whether the request targets the storefront homepage.
     */
    protected function isHomeRequest(Request $request): bool
    {
        return trim($request->getPathInfo(), '/') === '';
    }

    /**
     * Get the hash for the given request.
     */
    protected function getNormalizedRequestUri(Request $request): string
    {
        $pathInfo = $this->isHomeRequest($request) ? '/' : $request->getPathInfo();

        if (
            $request->routeIs('shop.search.index')
            && $request->has('query')
        ) {
            $queryString = "?query={$request->query('query')}";

            return $request->getBaseUrl().$pathInfo.$queryString;
        }

        return $request->getBaseUrl().$pathInfo;
    }

    /**
     * Get the cache name suffix for the given request.
     */
    protected function getCacheNameSuffix(Request $request): string
    {
        if ($request->attributes->has('responsecache.cacheNameSuffix')) {
            return $request->attributes->get('responsecache.cacheNameSuffix');
        }

        $cacheNameSuffix = core()->getCurrentChannel()->code
            .'-'.core()->getCurrentLocale()->code
            .'-'.core()->getCurrentCurrency()->code
            .'-'.$this->cacheProfile->useCacheNameSuffix($request);

        if ($this->isHomeRequest($request)) {
            $cacheNameSuffix .= '-hv'.static::getHomeCacheVersion();
        }

        return $cacheNameSuffix;
    }
}
