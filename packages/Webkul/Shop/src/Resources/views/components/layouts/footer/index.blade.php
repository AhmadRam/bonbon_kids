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
                <a href="https://www.instagram.com/bonbon_kuwait" target="_blank" aria-label="Instagram" class="text-toyBlue hover:opacity-75 transition-opacity">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="https://snapchat.com/t/ma2NUtXn" target="_blank" aria-label="Snapchat" class="text-toyBlue hover:opacity-75 transition-opacity">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.001 2c-3.116 0-5.467 2.05-5.69 4.79-.112 1.378.361 2.656 1.135 3.52-.393.585-1.157.943-2.029.98-.383.016-.763-.035-1.141-.122-.382-.087-.588-.302-.751-.555-.268-.415-.815-.556-1.258-.335-.453.227-.665.759-.481 1.222.383.96 1.261 1.625 2.296 1.776.321.047.647.054.966.02a3.785 3.785 0 001.077-.282c.11.332.257.653.447.962.593.964 1.488 1.688 2.585 2.012-.047.45-.148.887-.311 1.306-.211.54-.537.986-1.01 1.246-.532.291-1.026.541-1.396.906-.395.39-.636.937-.655 1.544a.75.75 0 101.498.053c.007-.225.074-.436.19-.606.11-.161.274-.297.476-.41.385-.213.917-.463 1.488-.775.541-.295.96-.757 1.223-1.428.106-.271.182-.555.23-.847.279.02.56.03.844.03.284 0 .565-.01.844-.03.048.292.124.576.23.847.263.671.682 1.133 1.223 1.428.571.312 1.103.562 1.488.775.202.113.366.249.476.41.116.17.183.381.19.606a.75.75 0 101.498-.053c-.019-.607-.26-1.154-.655-1.544-.37-.365-.864-.615-1.396-.906-.473-.26-.799-.706-1.01-1.246-.163-.419-.264-.856-.311-1.306 1.097-.324 1.992-1.048 2.585-2.012.19-.309.337-.63.447-.962.378.114.743.19 1.077.282.319.034.645.027.966-.02 1.035-.151 1.913-.816 2.296-1.776.184-.463-.028-.995-.481-1.222-.443-.221-.99-.08-1.258.335-.163.253-.369.468-.751.555-.378.087-.758.138-1.141.122-.872-.037-1.636-.395-2.029-.98.774-.864 1.247-2.142 1.135-3.52-.223-2.74-2.574-4.79-5.69-4.79z" />
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@bonbon.kuwait" target="_blank" aria-label="TikTok" class="text-toyBlue hover:opacity-75 transition-opacity">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.23-1.11 4.46-2.9 5.81-1.78 1.34-4.14 1.83-6.26 1.28-2.39-.62-4.45-2.54-5.06-4.96-.51-2.01-.22-4.19.86-5.94 1.16-1.89 3.23-3.13 5.37-3.35v4.06c-1.39.09-2.78.89-3.51 2.1-.81 1.34-.78 3.14.07 4.46.78 1.22 2.24 1.95 3.7 1.88 1.64-.08 3.12-1.35 3.39-2.99.16-.96.11-1.95.11-2.92V.02h.17z" />
                    </svg>
                </a>
                <a href="https://www.facebook.com/share/1Cw2H6hLBW/?mibextid=wwXIfr" target="_blank" aria-label="Facebook" class="text-toyBlue hover:opacity-75 transition-opacity">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="https://wa.me/+96592214430" target="_blank" aria-label="WhatsApp" class="text-toyBlue hover:opacity-75 transition-opacity">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.031 2C6.496 2 2 6.496 2 12.032c0 1.764.45 3.483 1.307 5.01L2.1 22l5.093-1.189A9.972 9.972 0 0012.031 22c5.535 0 10.03-4.497 10.03-10.033C22.062 6.497 17.566 2 12.031 2zm0 18.337c-1.503 0-2.981-.383-4.298-1.109l-.308-.17-3.19.744.757-3.111-.186-.312a8.318 8.318 0 01-1.272-4.382c0-4.607 3.75-8.358 8.36-8.358 4.61 0 8.361 3.75 8.361 8.358 0 4.607-3.751 8.358-8.361 8.358zM16.63 13.722c-.252-.126-1.492-.736-1.724-.82-.23-.083-.399-.126-.567.126-.168.253-.652.821-.798.99-.147.167-.294.188-.546.062-.252-.126-1.066-.393-2.03-1.253-.75-.67-1.257-1.498-1.405-1.75-.147-.253-.016-.39.11-.516.113-.113.252-.294.378-.44.126-.148.168-.252.252-.421.084-.168.042-.315-.021-.442-.063-.126-.567-1.368-.777-1.874-.204-.493-.413-.427-.567-.435-.147-.007-.315-.007-.483-.007s-.441.063-.672.316c-.23.252-.882.862-.882 2.1 0 1.238.903 2.436 1.029 2.604.126.168 1.775 2.712 4.298 3.8.6.258 1.068.412 1.433.528.602.19 1.15.163 1.583.099.484-.072 1.492-.61 1.702-1.199.21-.59.21-1.095.147-1.199-.063-.105-.231-.168-.483-.294z" />
                    </svg>
                </a>
                <a href="mailto:bonbon.MiddleEast@hotmail.com" target="_blank" aria-label="Email" class="text-toyBlue hover:opacity-75 transition-opacity">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
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
