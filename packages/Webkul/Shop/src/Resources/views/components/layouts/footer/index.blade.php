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

            <div class="flex flex-wrap justify-center gap-5">
                <!-- Email -->
                <a href="mailto:bonbon.MiddleEast@hotmail.com" target="_blank" aria-label="Email" class="hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#EA4335" />
                        <path fill="#fff" d="M18.8 7.5H5.2c-.66 0-1.2.54-1.2 1.2v6.6c0 .66.54 1.2 1.2 1.2h13.6c.66 0 1.2-.54 1.2-1.2v-6.6c0-.66-.54-1.2-1.2-1.2zm-1.04 1.6l-5.76 3.6-5.76-3.6h11.52zm-12.16.5l5.22 3.26c.42.26.94.26 1.36 0l5.22-3.26v5.3h-11.8v-5.3z" />
                    </svg>
                </a>
                
                <!-- WhatsApp -->
                <a href="https://wa.me/+96592214430" target="_blank" aria-label="WhatsApp" class="hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#25D366" d="M12.012 2.006c-5.518 0-9.998 4.48-9.998 9.998 0 1.764.462 3.484 1.34 5.006L2.012 22l5.122-1.344a9.96 9.96 0 004.878 1.266h.004c5.516 0 9.998-4.48 9.998-9.998 0-2.674-1.042-5.188-2.932-7.078A9.948 9.948 0 0012.012 2.006z"/>
                        <path fill="#fff" d="M17.484 13.568c-.3-.15-1.774-.876-2.048-.976-.274-.1-.474-.15-.674.15-.2.3-.774.976-.948 1.176-.176.2-.352.226-.652.076-.3-.15-1.268-.468-2.414-1.492-.892-.796-1.494-1.78-1.67-2.08-.176-.3-.018-.464.132-.614.134-.134.3-.35.45-.526.15-.176.2-.3.3-.5.1-.2.05-.376-.024-.526-.076-.15-.674-1.626-.924-2.226-.242-.584-.488-.504-.674-.514-.176-.008-.376-.008-.576-.008-.2 0-.526.076-.8.376-.274.3-1.048 1.026-1.048 2.502 0 1.476 1.074 2.902 1.224 3.102.15.2 2.114 3.226 5.12 4.522.716.308 1.274.492 1.71.63.718.228 1.37.196 1.884.118.576-.086 1.774-.726 2.024-1.428.25-.702.25-1.302.176-1.428-.076-.126-.276-.2-.576-.35z"/>
                    </svg>
                </a>
                
                <!-- Facebook -->
                <a href="https://www.facebook.com/share/1Cw2H6hLBW/?mibextid=wwXIfr" target="_blank" aria-label="Facebook" class="hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        <path fill="#fff" d="M16.671 15.542l.532-3.469h-3.328V9.823c0-.949.465-1.874 1.956-1.874h1.514V5.011s-1.374-.235-2.686-.235c-2.741 0-4.533 1.662-4.533 4.669v2.628H7.078v3.469h3.047v8.385a12.09 12.09 0 003.75 0v-8.385h2.796z"/>
                    </svg>
                </a>
                
                <!-- TikTok -->
                <a href="https://www.tiktok.com/@bonbon.kuwait" target="_blank" aria-label="TikTok" class="hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#000" />
                        <path fill="#fff" d="M13.05 3.95v7.6c0 1.3-.9 2.4-2.2 2.6-1.4.1-2.6-1-2.6-2.4 0-1.4 1-2.6 2.4-2.6v-1.9c-2.4 0-4.4 2-4.4 4.4s2 4.4 4.4 4.4c2.5 0 4.5-2 4.5-4.5V7.95c1.2.9 2.7 1.5 4.3 1.5v-1.9c-1.4 0-2.6-.5-3.6-1.4V3.95h-2.8z" />
                    </svg>
                </a>
                
                <!-- Snapchat -->
                <a href="https://snapchat.com/t/ma2NUtXn" target="_blank" aria-label="Snapchat" class="hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#FFFC00"/>
                        <path d="M12.001 5.911c-1.96 0-2.9 1.3-3.04 2.83-.04.42-.06.88-.12 1.36-.08.59-.23.92-.9.1.07-.52.12-1.07.2-1.52.41-.4.18-.59.5-.51.92.06.34.34.56.74.64.52.1 1.07.16 1.58.26.25.05.39.18.35.44-.06.32-.14.63-.27.92-.25.59-.68.9-1.29 1.04-.48.11-.92.21-1.13.68-.14.33-.03.66.26.9.24.21.58.27.89.28.97.02 1.93-.02 2.89-.1.32-.02.59.08.78.33.18.24.32.53.49.83.24.41.7.59 1.19.45.14-.04.25-.1.38-.16.49-.21.68-.26 1.19-.26.51 0 .7.06 1.19.26.13.06.25.12.38.16.48.14.94-.04 1.19-.45.17-.3.31-.6.49-.83.19-.25.46-.35.78-.33.97.08 1.93.12 2.89.1.32-.01.66-.07.89-.28.29-.24.41-.57.26-.9-.21-.47-.65-.57-1.13-.68-.61-.14-1.04-.45-1.29-1.04-.13-.29-.21-.6-.27-.92-.04-.26.1-.39.35-.44.51-.1 1.06-.16 1.58-.26.4-.08.68-.3.74-.64.06-.42-.11-.82-.51-.92-.46-.21-1.01-.29-1.54-.41-.66-.15-.81-.48-.89-1.07-.05-.48-.08-.94-.12-1.36-.14-1.53-1.08-2.83-3.04-2.83z" fill="#fff" stroke="#000" stroke-width="0.8"/>
                    </svg>
                </a>
                
                <!-- Instagram -->
                <a href="https://www.instagram.com/bonbon_kuwait" target="_blank" aria-label="Instagram" class="hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="ig-rg" r="1.5" cx=".2" cy="1.2">
                                <stop stop-color="#fd5" offset="0"/>
                                <stop stop-color="#ff543e" offset=".5"/>
                                <stop stop-color="#c837ab" offset="1"/>
                            </radialGradient>
                        </defs>
                        <rect width="24" height="24" rx="6" fill="url(#ig-rg)"/>
                        <path d="M12 7A5 5 0 1012 17 5 5 0 0012 7zm0 8a3 3 0 110-6 3 3 0 010 6zm4.5-8.25a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0zM19 8v8a4 4 0 01-4 4H9a4 4 0 01-4-4V8a4 4 0 014-4h6a4 4 0 014 4z" fill="#fff"/>
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
