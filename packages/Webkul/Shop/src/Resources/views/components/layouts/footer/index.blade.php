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
                <a href="mailto:bonbon.MiddleEast@hotmail.com" target="_blank" aria-label="Email" class="hover:-translate-y-1 transition-transform duration-300 shadow-sm rounded-[10px]">
                    <svg class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#ea4335" d="M14.017,41.99l20-0.019c4.4-0.004,8.003-3.592,8.008-7.992l0.019-20c0.004-4.4-3.592-8.003-7.992-8.008l-20,0.019c-4.4,0.004-8.003,3.592-8.008,7.992l-0.019,20 C6.014,38.383,9.617,41.986,14.017,41.99z"/>
                        <path fill="#ffffff" d="M35 15.5L24 23 13 15.5 13 15C13 13.9 13.9 13 15 13L33 13C34.1 13 35 13.9 35 15z"/>
                        <path fill="#ffffff" d="M35 33C35 34.1 34.1 35 33 35L15 35C13.9 35 13 34.1 13 33L13 17 24 24.5 35 17z"/>
                    </svg>
                </a>
                
                <!-- WhatsApp -->
                <a href="https://wa.me/+96592214430" target="_blank" aria-label="WhatsApp" class="hover:-translate-y-1 transition-transform duration-300 shadow-sm rounded-[10px]">
                    <svg class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#25d366" d="M14.017,41.99l20-0.019c4.4-0.004,8.003-3.592,8.008-7.992l0.019-20c0.004-4.4-3.592-8.003-7.992-8.008l-20,0.019c-4.4,0.004-8.003,3.592-8.008,7.992l-0.019,20 C6.014,38.383,9.617,41.986,14.017,41.99z"/>
                        <path fill="#ffffff" d="M33.682,14.301c-2.585-2.585-6.02-4.009-9.676-4.009c-7.534,0-13.664,6.13-13.664,13.664 c0,2.41,0.628,4.764,1.823,6.837l-1.94,7.081l7.243-1.9c2.012,1.087,4.286,1.66,6.536,1.66h0.006 c7.532,0,13.662-6.131,13.662-13.664C37.671,20.316,36.248,16.886,33.682,14.301z M24.006,35.297h-0.005 c-2.042,0-4.045-0.549-5.795-1.587l-0.415-0.246l-4.305,1.129l1.149-4.195l-0.271-0.431c-1.141-1.815-1.741-3.916-1.741-6.08 c0-6.257,5.091-11.347,11.353-11.347c3.033,0,5.882,1.182,8.026,3.328c2.144,2.145,3.325,4.995,3.324,8.028 C35.326,30.153,30.237,35.297,24.006,35.297z M30.229,26.793c-0.341-0.171-2.021-0.997-2.333-1.111 c-0.312-0.114-0.539-0.171-0.767,0.171c-0.227,0.342-0.88,1.111-1.079,1.339c-0.199,0.228-0.398,0.256-0.739,0.085 c-0.341-0.171-1.442-0.531-2.747-1.696c-1.015-0.906-1.7-2.025-1.899-2.367c-0.199-0.342-0.021-0.526,0.15-0.697 c0.153-0.153,0.341-0.398,0.512-0.598c0.171-0.199,0.227-0.341,0.341-0.569c0.114-0.228,0.057-0.427-0.028-0.598 c-0.085-0.171-0.767-1.85-1.051-2.533c-0.277-0.665-0.559-0.575-0.767-0.585c-0.199-0.01-0.427-0.01-0.654-0.01 c-0.227,0-0.597,0.085-0.91,0.427c-0.313,0.341-1.194,1.167-1.194,2.845s1.222,3.299,1.393,3.527 c0.171,0.228,2.405,3.672,5.826,5.148c0.814,0.351,1.45,0.561,1.947,0.718c0.817,0.26,1.56,0.223,2.146,0.135 c0.655-0.098,2.021-0.825,2.305-1.622c0.284-0.797,0.284-1.48,0.199-1.622C30.769,26.964,30.542,26.879,30.229,26.793z"/>
                    </svg>
                </a>
                
                <!-- Facebook -->
                <a href="https://www.facebook.com/share/1Cw2H6hLBW/?mibextid=wwXIfr" target="_blank" aria-label="Facebook" class="hover:-translate-y-1 transition-transform duration-300 shadow-sm rounded-[10px]">
                    <svg class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#1877f2" d="M14.017,41.99l20-0.019c4.4-0.004,8.003-3.592,8.008-7.992l0.019-20c0.004-4.4-3.592-8.003-7.992-8.008l-20,0.019c-4.4,0.004-8.003,3.592-8.008,7.992l-0.019,20 C6.014,38.383,9.617,41.986,14.017,41.99z"/>
                        <path fill="#ffffff" d="M30.406,30.94l1.064-6.94h-6.656v-4.5c0-1.9,0.93-3.75,3.911-3.75h3.026V9.844 c0,0-2.747-0.469-5.372-0.469c-5.482,0-9.065,3.323-9.065,9.337v5.288h-6.094v6.94h6.094v16.768 C18.995,47.92,20.237,48,21.5,48c1.262,0,2.505-0.08,3.75-0.232V30.94H30.406z"/>
                    </svg>
                </a>
                
                <!-- TikTok -->
                <a href="https://www.tiktok.com/@bonbon.kuwait" target="_blank" aria-label="TikTok" class="hover:-translate-y-1 transition-transform duration-300 shadow-sm rounded-[10px]">
                    <svg class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#000000" d="M14.017,41.99l20-0.019c4.4-0.004,8.003-3.592,8.008-7.992l0.019-20c0.004-4.4-3.592-8.003-7.992-8.008l-20,0.019c-4.4,0.004-8.003,3.592-8.008,7.992l-0.019,20 C6.014,38.383,9.617,41.986,14.017,41.99z"/>
                        <path fill="#00f2fe" d="M25.542,10.052v12.228c0,2.091-1.448,3.861-3.54,4.182c-2.253,0.161-4.182-1.608-4.182-3.861 s1.608-4.182,3.861-4.182v-3.056c-3.861,0-7.077,3.216-7.077,7.077s3.216,7.077,7.077,7.077c4.021,0,7.237-3.216,7.237-7.237V16.48 c1.93,1.448,4.343,2.413,6.916,2.413v-3.056c-2.253,0-4.182-0.804-5.79-2.253v-3.532H25.542z"/>
                        <path fill="#fe004f" d="M25.059,9.569v12.228c0,2.091-1.448,3.861-3.54,4.182c-2.253,0.161-4.182-1.608-4.182-3.861 c0-2.253,1.608-4.182,3.861-4.182V14.88c-3.861,0-7.077,3.216-7.077,7.077s3.216,7.077,7.077,7.077c4.021,0,7.237-3.216,7.237-7.237 V15.998c1.93,1.448,4.343,2.413,6.916,2.413v-3.056c-2.253,0-4.182-0.804-5.79-2.253V9.569H25.059z"/>
                        <path fill="#ffffff" d="M25.3,9.811v12.228c0,2.091-1.448,3.861-3.54,4.182c-2.253,0.161-4.182-1.608-4.182-3.861 c0-2.253,1.608-4.182,3.861-4.182v-3.056c-3.861,0-7.077,3.216-7.077,7.077s3.216,7.077,7.077,7.077c4.021,0,7.237-3.216,7.237-7.237 V16.239c1.93,1.448,4.343,2.413,6.916,2.413v-3.056c-2.253,0-4.182-0.804-5.79-2.253V9.811H25.3z"/>
                    </svg>
                </a>
                
                <!-- Snapchat -->
                <a href="https://snapchat.com/t/ma2NUtXn" target="_blank" aria-label="Snapchat" class="hover:-translate-y-1 transition-transform duration-300 shadow-sm rounded-[10px]">
                    <svg class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#fffc00" d="M14.017,41.99l20-0.019c4.4-0.004,8.003-3.592,8.008-7.992l0.019-20c0.004-4.4-3.592-8.003-7.992-8.008l-20,0.019c-4.4,0.004-8.003,3.592-8.008,7.992l-0.019,20 C6.014,38.383,9.617,41.986,14.017,41.99z"/>
                        <path d="M24.032,12.002c-3.413,0-5.056,2.269-5.293,4.929c-0.069,0.724-0.111,1.531-0.209,2.366 c-0.139,1.03-0.404,1.601-1.56,1.865c-0.905,0.209-1.866,0.348-2.673,0.71 c-0.696,0.32-1.03,0.863-0.891,1.615c0.111,0.599,0.599,0.988,1.295,1.128c0.905,0.181,1.88,0.278,2.785,0.459 c0.445,0.083,0.682,0.32,0.612,0.766c-0.097,0.557-0.25,1.1-0.473,1.615c-0.445,1.03-1.197,1.587-2.27,1.824 c-0.835,0.195-1.615,0.376-1.977,1.197c-0.25,0.585-0.056,1.156,0.46,1.587c0.418,0.362,1.016,0.473,1.56,0.487 c1.699,0.028,3.398-0.028,5.083-0.167c0.557-0.042,1.03,0.139,1.365,0.585c0.32,0.418,0.557,0.933,0.863,1.462 c0.418,0.724,1.225,1.044,2.061,0.794c0.237-0.07,0.445-0.181,0.668-0.278c0.863-0.362,1.197-0.46,2.089-0.46 s1.225,0.097,2.089,0.46c0.223,0.097,0.432,0.209,0.668,0.278c0.835,0.25,1.643-0.07,2.061-0.794 c0.306-0.529,0.543-1.044,0.863-1.462c0.334-0.445,0.808-0.626,1.365-0.585c1.685,0.139,3.384,0.195,5.083,0.167 c0.557-0.014,1.142-0.125,1.56-0.487c0.515-0.432,0.71-1.002,0.46-1.587c-0.362-0.821-1.142-1.002-1.977-1.197 c-1.072-0.237-1.824-0.794-2.27-1.824c-0.223-0.515-0.376-1.058-0.473-1.615c-0.07-0.446,0.167-0.682,0.612-0.766 c0.905-0.181,1.88-0.278,2.785-0.459c0.696-0.139,1.183-0.529,1.295-1.128c0.139-0.752-0.195-1.295-0.891-1.615 c-0.808-0.362-1.768-0.501-2.673-0.71c-1.156-0.264-1.42-0.835-1.56-1.865c-0.097-0.835-0.139-1.643-0.209-2.366 C29.088,14.271,27.445,12.002,24.032,12.002z" fill="#000000"/>
                    </svg>
                </a>
                
                <!-- Instagram -->
                <a href="https://www.instagram.com/bonbon_kuwait" target="_blank" aria-label="Instagram" class="hover:-translate-y-1 transition-transform duration-300 shadow-sm rounded-[10px]">
                    <svg class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <radialGradient id="ig-rg-2" cx="19.38" cy="42.035" r="44.899" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#fd5"/><stop offset=".328" stop-color="#ff543f"/><stop offset=".348" stop-color="#fc5245"/><stop offset=".504" stop-color="#e64771"/><stop offset=".643" stop-color="#d53e91"/><stop offset=".761" stop-color="#cc39a4"/><stop offset=".841" stop-color="#c837ab"/></radialGradient>
                        <path fill="url(#ig-rg-2)" d="M34.017,41.99l-20,0.019c-4.4,0.004-8.003-3.592-8.008-7.992l-0.019-20 c-0.004-4.4,3.592-8.003,7.992-8.008l20-0.019c4.4-0.004,8.003,3.592,8.008,7.992l0.019,20 C42.014,38.383,38.417,41.986,34.017,41.99z"/>
                        <radialGradient id="ig-rg-3" cx="11.786" cy="5.54" r="29.813" gradientTransform="matrix(1 0 0 .6663 0 1.849)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#4168c9"/><stop offset=".999" stop-color="#4168c9" stop-opacity="0"/></radialGradient>
                        <path fill="url(#ig-rg-3)" d="M34.017,41.99l-20,0.019c-4.4,0.004-8.003-3.592-8.008-7.992l-0.019-20 c-0.004-4.4,3.592-8.003,7.992-8.008l20-0.019c4.4-0.004,8.003,3.592,8.008,7.992l0.019,20 C42.014,38.383,38.417,41.986,34.017,41.99z"/>
                        <path fill="#fff" d="M24,31c-3.859,0-7-3.14-7-7s3.141-7,7-7s7,3.14,7,7S27.859,31,24,31z M24,19c-2.757,0-5,2.243-5,5 s2.243,5,5,5s5-2.243,5-5S26.757,19,24,19z"/>
                        <circle cx="31.5" cy="16.5" r="1.5" fill="#fff"/>
                        <path fill="#fff" d="M30,37H18c-3.859,0-7-3.14-7-7V18c0-3.86,3.141-7,7-7h12c3.859,0,7,3.14,7,7v12 C37,33.86,33.859,37,30,37z M18,13c-2.757,0-5,2.243-5,5v12c0,2.757,2.243,5,5,5h12c2.757,0,5-2.243,5-5V18c0-2.757-2.243-5-5-5H18z"/>
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
