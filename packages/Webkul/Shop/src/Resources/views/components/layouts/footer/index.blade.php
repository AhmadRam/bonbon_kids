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

            <!-- تحميل مكتبة FontAwesome 6 لعرض الأيقونات -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

            <div class="flex flex-wrap justify-center gap-5">
                <!-- Email -->
                <a href="mailto:bonbon.MiddleEast@hotmail.com" target="_blank" aria-label="Email" class="hover:-translate-y-1 transition-transform duration-300">
                    <i class="fa-solid fa-envelope text-[32px]" style="color: #EA4335;"></i>
                </a>
                
                <!-- WhatsApp -->
                <a href="https://wa.me/+96592214430" target="_blank" aria-label="WhatsApp" class="hover:-translate-y-1 transition-transform duration-300">
                    <i class="fa-brands fa-whatsapp text-[32px]" style="color: #25D366;"></i>
                </a>
                
                <!-- Facebook -->
                <a href="https://www.facebook.com/share/1Cw2H6hLBW/?mibextid=wwXIfr" target="_blank" aria-label="Facebook" class="hover:-translate-y-1 transition-transform duration-300">
                    <i class="fa-brands fa-facebook text-[32px]" style="color: #1877F2;"></i>
                </a>
                
                <!-- TikTok -->
                <a href="https://www.tiktok.com/@bonbon.kuwait" target="_blank" aria-label="TikTok" class="hover:-translate-y-1 transition-transform duration-300">
                    <i class="fa-brands fa-tiktok text-[32px]" style="color: #000000;"></i>
                </a>
                
                <!-- Snapchat -->
                <a href="https://snapchat.com/t/ma2NUtXn" target="_blank" aria-label="Snapchat" class="hover:-translate-y-1 transition-transform duration-300">
                    <i class="fa-brands fa-snapchat text-[32px] drop-shadow-md" style="color: #FFFC00;"></i>
                </a>
                
                <!-- Instagram -->
                <a href="https://www.instagram.com/bonbon_kuwait" target="_blank" aria-label="Instagram" class="hover:-translate-y-1 transition-transform duration-300">
                    <i class="fa-brands fa-instagram text-[32px]" style="color: #E1306C;"></i>
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
