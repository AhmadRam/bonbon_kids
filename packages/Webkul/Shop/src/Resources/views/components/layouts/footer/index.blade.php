{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category repository is injected directly here because there is no way
    to retrieve it from the view composer, as this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $channel = core()->getCurrentChannel();

    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'theme_code' => $channel->theme,
        'channel_id' => $channel->id,
    ]);
@endphp

<footer class="mt-9 bg-toyLightBlue max-sm:mt-10 border-t-2 border-toyBlue">
    <div class="flex justify-between gap-x-6 gap-y-8 p-[60px] max-1060:flex-col-reverse max-md:gap-5 max-md:p-8 max-sm:px-4 max-sm:py-5">
        <!-- For Desktop View -->
        <div
            class="flex flex-wrap items-start gap-24 max-1180:gap-6 max-1060:hidden"
            v-pre
        >
            @if ($customization?->options)
                @foreach ($customization->options as $footerLinkSection)
                    <ul class="grid gap-5 text-sm">
                        @php
                            usort($footerLinkSection, function ($a, $b) {
                                return $a['sort_order'] - $b['sort_order'];
                            });
                        @endphp

                        @foreach ($footerLinkSection as $link)
                            <li>
                                <a href="{{ $link['url'] }}">
                                    {{ $link['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            @endif
        </div>

        <!-- For Mobile view -->
        <x-shop::accordion
            :is-active="false"
            class="hidden !w-full rounded-xl !border-2 !border-[#e9decc] max-1060:block max-sm:rounded-lg"
        >
            <x-slot:header class="rounded-t-lg bg-[#F1EADF] font-medium max-md:p-2.5 max-sm:px-3 max-sm:py-2 max-sm:text-sm">
                @lang('shop::app.components.layouts.footer.footer-content')
            </x-slot>

            <x-slot:content class="flex justify-between !bg-transparent !p-4">
                @if ($customization?->options)
                    @foreach ($customization->options as $footerLinkSection)
                        <ul
                            class="grid gap-5 text-sm"
                            v-pre
                        >
                            @php
                                usort($footerLinkSection, function ($a, $b) {
                                    return $a['sort_order'] - $b['sort_order'];
                                });
                            @endphp

                            @foreach ($footerLinkSection as $link)
                                <li>
                                    <a
                                        href="{{ $link['url'] }}"
                                        class="text-sm font-medium max-sm:text-xs"
                                    >
                                        {{ $link['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                @endif
            </x-slot>
        </x-shop::accordion>

        {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.before') !!}

        <div class="flex flex-col items-center justify-center gap-6">
            <a href="{{ route('shop.home.index') }}" aria-label="{{ config('app.name') }}">
                <img
                    src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                    class="max-w-[200px]"
                >
            </a>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://www.instagram.com/bonbon_kuwait" target="_blank" aria-label="Instagram" class="hover:scale-110 transition-transform duration-300">
                    <svg viewBox="0 0 24 24" width="28" height="28" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="ig-rg" r="1.5" cx=".2" cy="1.2">
                                <stop stop-color="#fd5" offset="0"/>
                                <stop stop-color="#fd5" offset=".1"/>
                                <stop stop-color="#ff543e" offset=".5"/>
                                <stop stop-color="#c837ab" offset="1"/>
                            </radialGradient>
                        </defs>
                        <rect width="24" height="24" rx="6" fill="url(#ig-rg)"/>
                        <path d="M12 7A5 5 0 1012 17 5 5 0 0012 7zm0 8a3 3 0 110-6 3 3 0 010 6zm4.5-8.25a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0zM19 8v8a4 4 0 01-4 4H9a4 4 0 01-4-4V8a4 4 0 014-4h6a4 4 0 014 4z" fill="#fff"/>
                    </svg>
                </a>
                <a href="https://snapchat.com/t/ma2NUtXn" target="_blank" aria-label="Snapchat" class="hover:scale-110 transition-transform duration-300">
                    <svg width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#FFFC00" d="M12 0c6.627 0 12 5.373 12 12s-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0z"/>
                        <path d="M12 4.41c-2.45 0-3.63 1.63-3.8 3.54-.05.52-.08 1.1-.15 1.7-.1.74-.29 1.15-1.12 1.34-.65.15-1.34.25-1.92.51-.5.23-.74.62-.64 1.16.08.43.43.71.93.81.65.13 1.35.2 2 .33.32.06.49.23.44.55-.07.4-.18.79-.34 1.16-.32.74-.86 1.14-1.63 1.31-.6.14-1.16.27-1.42.86-.18.42-.04.83.33 1.14.3.26.73.34 1.12.35 1.22.02 2.44-.02 3.65-.12.4-.03.74.1.98.42.23.3.4.67.62 1.05.3.52.88.75 1.48.57.17-.05.32-.13.48-.2.62-.26.86-.33 1.5-.33.64 0 .88.07 1.5.33.16.07.31.15.48.2.6.18 1.18-.05 1.48-.57.22-.38.39-.75.62-1.05.24-.32.58-.45.98-.42 1.21.1 2.43.14 3.65.12.4-.01.82-.09 1.12-.35.37-.31.51-.72.33-1.14-.26-.59-.82-.72-1.42-.86-.77-.17-1.31-.57-1.63-1.31-.16-.37-.27-.76-.34-1.16-.05-.32.12-.49.44-.55.65-.13 1.35-.2 2-.33.5-.1.85-.38.93-.81.1-.54-.14-.93-.64-1.16-.58-.26-1.27-.36-1.92-.51-.83-.19-1.02-.6-1.12-1.34-.07-.6-.1-1.18-.15-1.7-.17-1.91-1.35-3.54-3.8-3.54z" fill="#000"/>
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@bonbon.kuwait" target="_blank" aria-label="TikTok" class="hover:scale-110 transition-transform duration-300">
                    <svg width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#000" d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z"/>
                        <path fill="#00f2fe" d="M13.2 4.1v7.6c0 1.3-.9 2.4-2.2 2.6-1.4.1-2.6-1-2.6-2.4s1-2.6 2.4-2.6V7.4c-2.4 0-4.4 2-4.4 4.4s2 4.4 4.4 4.4c2.5 0 4.5-2 4.5-4.5V8.1c1.2.9 2.7 1.5 4.3 1.5V7.7c-1.4 0-2.6-.5-3.6-1.4V4.1h-2.8z"/>
                        <path fill="#fe004f" d="M12.9 3.8v7.6c0 1.3-.9 2.4-2.2 2.6-1.4.1-2.6-1-2.6-2.4 0-1.4 1-2.6 2.4-2.6V7.1c-2.4 0-4.4 2-4.4 4.4s2 4.4 4.4 4.4c2.5 0 4.5-2 4.5-4.5V7.8c1.2.9 2.7 1.5 4.3 1.5V7.4c-1.4 0-2.6-.5-3.6-1.4V3.8h-2.8z"/>
                        <path fill="#fff" d="M13.05 3.95v7.6c0 1.3-.9 2.4-2.2 2.6-1.4.1-2.6-1-2.6-2.4 0-1.4 1-2.6 2.4-2.6v-1.9c-2.4 0-4.4 2-4.4 4.4s2 4.4 4.4 4.4c2.5 0 4.5-2 4.5-4.5V7.95c1.2.9 2.7 1.5 4.3 1.5v-1.9c-1.4 0-2.6-.5-3.6-1.4V3.95h-2.8z"/>
                    </svg>
                </a>
                <a href="https://www.facebook.com/share/1Cw2H6hLBW/?mibextid=wwXIfr" target="_blank" aria-label="Facebook" class="hover:scale-110 transition-transform duration-300">
                    <svg width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#1877F2">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        <path fill="#fff" d="M16.671 15.542l.532-3.469h-3.328V9.823c0-.949.465-1.874 1.956-1.874h1.514V5.011s-1.374-.235-2.686-.235c-2.741 0-4.533 1.662-4.533 4.669v2.628H7.078v3.469h3.047v8.385a12.09 12.09 0 003.75 0v-8.385h2.796z"/>
                    </svg>
                </a>
                <a href="https://wa.me/+96592214430" target="_blank" aria-label="WhatsApp" class="hover:scale-110 transition-transform duration-300">
                    <svg width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#25D366">
                        <path d="M12.012 2.006c-5.518 0-9.998 4.48-9.998 9.998 0 1.764.462 3.484 1.34 5.006L2.012 22l5.122-1.344a9.96 9.96 0 004.878 1.266h.004c5.516 0 9.998-4.48 9.998-9.998 0-2.674-1.042-5.188-2.932-7.078A9.948 9.948 0 0012.012 2.006z"/>
                        <path fill="#fff" d="M17.484 13.568c-.3-.15-1.774-.876-2.048-.976-.274-.1-.474-.15-.674.15-.2.3-.774.976-.948 1.176-.176.2-.352.226-.652.076-.3-.15-1.268-.468-2.414-1.492-.892-.796-1.494-1.78-1.67-2.08-.176-.3-.018-.464.132-.614.134-.134.3-.35.45-.526.15-.176.2-.3.3-.5.1-.2.05-.376-.024-.526-.076-.15-.674-1.626-.924-2.226-.242-.584-.488-.504-.674-.514-.176-.008-.376-.008-.576-.008-.2 0-.526.076-.8.376-.274.3-1.048 1.026-1.048 2.502 0 1.476 1.074 2.902 1.224 3.102.15.2 2.114 3.226 5.12 4.522.716.308 1.274.492 1.71.63.718.228 1.37.196 1.884.118.576-.086 1.774-.726 2.024-1.428.25-.702.25-1.302.176-1.428-.076-.126-.276-.2-.576-.35z"/>
                    </svg>
                </a>
                <a href="mailto:bonbon.MiddleEast@hotmail.com" target="_blank" aria-label="Email" class="hover:scale-110 transition-transform duration-300">
                    <svg width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#EA4335" d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                        <path fill="#fff" d="m3.116 6.134 8.442 5.2a.75.75 0 0 0 .786 0l8.44-5.197a1.5 1.5 0 0 0-2.146-2.02l-6.687 4.116L5.264 4.116a1.5 1.5 0 0 0-2.148 2.018Z" />
                    </svg>
                </a>
            </div>
        </div>

        {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.after') !!}
    </div>

    <div class="flex justify-between bg-toyBlue px-[60px] py-3.5 max-md:justify-center max-sm:px-5 text-white">
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}

        <p class="text-sm max-md:text-center">
            @if (core()->getConfigData('general.content.footer.copyright_content'))
                {!! core()->getConfigData('general.content.footer.copyright_content') !!}
            @else
                @lang('shop::app.components.layouts.footer.footer-text', ['current_year'=> date('Y') ])
            @endif
        </p>

        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
