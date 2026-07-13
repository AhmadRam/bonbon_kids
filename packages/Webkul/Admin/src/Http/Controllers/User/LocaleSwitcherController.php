<?php

namespace Webkul\Admin\Http\Controllers\User;

use Illuminate\Http\RedirectResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Repositories\LocaleRepository;

class LocaleSwitcherController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected LocaleRepository $localeRepository) {}

    /**
     * Switch the admin UI locale and redirect back.
     */
    public function switch(): RedirectResponse
    {
        $localeCode = request()->get('code');

        if ($localeCode) {
            $locale = $this->localeRepository->findOneByField('code', $localeCode);

            if ($locale) {
                session()->put('admin_locale', $localeCode);
            }
        }

        return redirect()->back();
    }
}
