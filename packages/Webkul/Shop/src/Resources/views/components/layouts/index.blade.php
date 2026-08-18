@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-W7RG99P6');</script>
        <!-- End Google Tag Manager -->

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-TVSXRGT2EB"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'G-TVSXRGT2EB');
        </script>
        <!-- End Google tag -->

        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            http-equiv="content-language"
            content="{{ app()->getLocale() }}"
        >

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >
        <meta
            name="generator"
            content="Bagisto"
        >

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
            crossorigin
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />

        <link
            rel="preload" as="style"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        @stack('styles')

        <style>
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        @if(core()->getConfigData('general.content.speculation_rules.enabled'))
            <script type="speculationrules">
                @json(core()->getSpeculationRules(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            </script>
        @endif

        {!! view_render_event('bagisto.shop.layout.head.after') !!}

    </head>

    <body>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W7RG99P6"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        <a
            href="#main"
            class="skip-to-main-content-link"
        >
            Skip to main content
        </a>

        <!-- Built With Bagisto -->
        <div id="app">
            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <!-- Add to Cart Modal Blade Component -->
            <x-shop::modal.add-to-cart />

            <!-- Page Header Blade Component -->
            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            @if(
                core()->getConfigData('general.gdpr.settings.enabled')
                && core()->getConfigData('general.gdpr.cookie.enabled')
            )
                <x-shop::layouts.cookie />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <!-- Page Content Blade Component -->
            <main id="main" class="bg-white">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}


            <!-- Page Services Blade Component -->
            @if ($hasFeature)
                <x-shop::layouts.services />
            @endif

            <!-- Page Footer Blade Component -->
            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif
        </div>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}
        <script>
            /**
             * Load event, the purpose of using the event is to mount the application
             * after all of our `Vue` components which is present in blade file have
             * been registered in the app. No matter what `app.mount()` should be
             * called in the last.
             */
            window.addEventListener("load", function (event) {
                app.mount("#app");

                if (window.axios) {
                    window.axios.interceptors.response.use(function (response) {
                        try {
                            let url = response.config.url || '';
                            let method = (response.config.method || '').toLowerCase();
                            
                            if (url.includes('/api/checkout/cart') && method === 'post' && !url.includes('destroy') && !url.includes('update')) {
                                let productId = null;
                                let quantity = 1;
                                
                                if (typeof response.config.data === 'string') {
                                    let reqData = JSON.parse(response.config.data);
                                    productId = reqData.product_id;
                                    quantity = reqData.quantity || 1;
                                } else if (response.config.data instanceof FormData) {
                                    productId = response.config.data.get('product_id');
                                    quantity = response.config.data.get('quantity') || 1;
                                } else if (response.config.data) {
                                    productId = response.config.data.product_id;
                                    quantity = response.config.data.quantity || 1;
                                }

                                let cart = response.data.data;
                                if (cart && cart.items && productId) {
                                    let item = cart.items.find(i => i.product_id == productId);
                                    if (item) {
                                        window.dataLayer = window.dataLayer || [];
                                        window.dataLayer.push({ ecommerce: null });
                                        let price = item.formatted_price ? parseFloat(item.formatted_price.replace(/[^0-9.]/g, '')) : 0;
                                        window.dataLayer.push({
                                            event: 'add_to_cart',
                                            ecommerce: {
                                                currency: '{{ core()->getCurrentCurrencyCode() }}',
                                                value: price * quantity,
                                                items: [{
                                                    item_id: item.sku || item.product_url_key,
                                                    item_name: item.name,
                                                    price: price,
                                                    quantity: quantity
                                                }]
                                            }
                                        });
                                    }
                                }
                            }

                            if (url.includes('/api/checkout/cart/destroy') && method === 'post') {
                                let cartItemId = null;
                                if (typeof response.config.data === 'string') {
                                    let reqData = JSON.parse(response.config.data);
                                    cartItemId = reqData.cart_item_id;
                                } else if (response.config.data instanceof FormData) {
                                    cartItemId = response.config.data.get('cart_item_id');
                                } else if (response.config.data) {
                                    cartItemId = response.config.data.cart_item_id;
                                }
                                
                                window.dataLayer = window.dataLayer || [];
                                window.dataLayer.push({ ecommerce: null });
                                window.dataLayer.push({
                                    event: 'remove_from_cart',
                                    ecommerce: {
                                        currency: '{{ core()->getCurrentCurrencyCode() }}',
                                        items: [{
                                            item_id: cartItemId || 'unknown'
                                        }]
                                    }
                                });
                            }
                        } catch (e) {}
                        
                        return response;
                    }, function (error) {
                        return Promise.reject(error);
                    });
                }
            });
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
    </body>
</html>
