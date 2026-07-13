<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Core\Repositories\LocaleRepository;

class AdminLocale
{
    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected LocaleRepository $localeRepository) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $localeCode = $request->get('admin_locale_code');

        if ($localeCode) {
            $locale = $this->localeRepository->findOneByField('code', $localeCode);

            if ($locale) {
                session()->put('admin_locale', $localeCode);
            }
        }

        $sessionLocale = session()->get('admin_locale');

        if ($sessionLocale) {
            $locale = $this->localeRepository->findOneByField('code', $sessionLocale);

            if ($locale) {
                app()->setLocale($sessionLocale);
            }
        }

        return $next($request);
    }
}
