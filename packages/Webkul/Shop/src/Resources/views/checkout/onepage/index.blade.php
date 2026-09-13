<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="@lang('shop::app.checkout.onepage.index.checkout')"/>

    <meta name="keywords" content="@lang('shop::app.checkout.onepage.index.checkout')"/>
@endPush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.checkout.onepage.index.checkout')
    </x-slot>

    {!! view_render_event('bagisto.shop.checkout.onepage.header.before') !!}

    <!-- Page Header -->
    <div class="flex-wrap">
        <div class="flex w-full justify-between border border-b border-l-0 border-r-0 border-t-0 px-[60px] py-4 max-lg:px-8 max-sm:px-4">
            <div class="flex items-center gap-x-14 max-[1180px]:gap-x-9">
                <a
                    href="{{ route('shop.home.index') }}"
                    class="flex min-h-[30px]"
                    aria-label="@lang('shop::checkout.onepage.index.bagisto')"
                >
                    <img
                        src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
                        alt="{{ config('app.name') }}"
                        width="131"
                        height="29"
                    >
                </a>
            </div>

            @guest('customer')
                @include('shop::checkout.login')
            @endguest
        </div>
    </div>

    {!! view_render_event('bagisto.shop.checkout.onepage.header.after') !!}

    <!-- Page Content -->
    <div class="container px-[60px] max-lg:px-8 max-sm:px-4">

        {!! view_render_event('bagisto.shop.checkout.onepage.breadcrumbs.before') !!}

        <!-- Breadcrumbs -->
        @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
            <x-shop::breadcrumbs name="checkout" />
        @endif

        {!! view_render_event('bagisto.shop.checkout.onepage.breadcrumbs.after') !!}

        <!-- Checkout Vue Component -->
        <v-checkout>
            <!-- Shimmer Effect -->
            <x-shop::shimmer.checkout.onepage />
        </v-checkout>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-checkout-template"
        >
            <template v-if="! cart">
                <!-- Shimmer Effect -->
                <x-shop::shimmer.checkout.onepage />
            </template>

            <template v-else>
                <div class="grid grid-cols-[1fr_auto] gap-8 max-lg:grid-cols-[1fr] max-md:gap-5">
                    <!-- Included Checkout Summary Blade File For Mobile view -->
                    <div class="hidden max-md:block">
                        @include('shop::checkout.onepage.summary')
                    </div>

                    <div
                        class="overflow-y-auto max-md:grid max-md:gap-4"
                        id="steps-container"
                    >
                        <!-- Included Addresses Blade File -->
                        @include('shop::checkout.onepage.address')

                        <!-- Included Payment Methods Blade File -->
                        @include('shop::checkout.onepage.payment')
                    </div>

                    <!-- Included Checkout Summary Blade File For Desktop view -->
                    <div class="sticky top-8 block h-max w-[442px] max-w-full max-lg:w-auto max-lg:max-w-[442px] ltr:pl-8 max-lg:ltr:pl-0 rtl:pr-8 max-lg:rtl:pr-0">
                        <div class="block max-md:hidden">
                            @include('shop::checkout.onepage.summary')
                        </div>

                        <div
                            class="flex justify-end"
                            v-if="canPlaceOrder"
                        >
                            <template v-if="cart.payment_method == 'paypal_smart_button'">
                                {!! view_render_event('bagisto.shop.checkout.onepage.summary.paypal_smart_button.before') !!}

                                <!-- Paypal Smart Button Vue Component -->
                                <v-paypal-smart-button></v-paypal-smart-button>

                                {!! view_render_event('bagisto.shop.checkout.onepage.summary.paypal_smart_button.after') !!}
                            </template>

                            <template v-else>
                                <x-shop::button
                                    type="button"
                                    class="primary-button w-max rounded-2xl bg-navyBlue px-11 py-3 max-md:mb-4 max-md:w-full max-md:max-w-full max-md:rounded-lg max-sm:py-1.5"
                                    :title="trans('shop::app.checkout.onepage.summary.place-order')"
                                    ::disabled="isPlacingOrder"
                                    ::loading="isPlacingOrder"
                                    @click="placeOrder"
                                />
                            </template>
                        </div>

                        <!-- Free Gift Wrapping Notice for Mobile view (Placed AFTER Place Order button) -->
                        <div class="block md:hidden">
                            @include('shop::checkout.onepage.gift-wrapping')
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-checkout', {
                template: '#v-checkout-template',

                data() {
                    return {
                        cart: null,

                        displayTax: {
                            prices: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_prices') }}",

                            subtotal: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_subtotal') }}",
                            
                            shipping: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_shipping_amount') }}",
                        },

                        isPlacingOrder: false,

                        currentStep: 'review',

                        shippingMethods: null,

                        paymentMethods: null,

                        selectedPaymentMethod: null,

                        canPlaceOrder: true,

                        hasSentBeginCheckout: false,
                    }
                },

                mounted() {
                    this.getCart();

                    this.$emitter.on('address-validation-failed', () => {
                        this.isPlacingOrder = false;
                    });

                    this.$emitter.on('address-validated-ready', (data) => {
                        this.processCompleteCheckout(data);
                    });
                },

                beforeUnmount() {
                    this.$emitter.off('address-validation-failed');
                    this.$emitter.off('address-validated-ready');
                },

                methods: {
                    getCart() {
                        this.$axios.get("{{ route('shop.checkout.onepage.summary') }}")
                            .then(response => {
                                this.cart = response.data.data;

                                if (this.cart?.payment_methods) {
                                    this.paymentMethods = this.cart.payment_methods;
                                }

                                if (!this.hasSentBeginCheckout) {
                                    this.pushBeginCheckout(this.cart);
                                    this.hasSentBeginCheckout = true;
                                }
                            })
                            .catch(error => {});
                    },

                    paymentSelected(payment) {
                        this.selectedPaymentMethod = payment.payment || payment.method || payment;
                    },

                    stepForward(step) {
                        this.currentStep = step;
                    },

                    stepProcessed(data) {
                        this.getCart();
                    },

                    placeOrder() {
                        if (this.isPlacingOrder) {
                            return;
                        }

                        this.isPlacingOrder = true;

                        // Trigger address validation and submission in the child address component
                        this.$emitter.emit('trigger-address-submit');
                    },

                    async processCompleteCheckout({ params, setErrors }) {
                        try {
                            // 1. Store Address
                            let addressResponse = await this.$axios.post('{{ route('shop.checkout.onepage.addresses.store') }}', params);

                            if (addressResponse.data.data?.redirect_url) {
                                window.location.href = addressResponse.data.data.redirect_url;
                                return;
                            }

                            // 2. Auto-select and save shipping rate if stockable items
                            if (this.cart.have_stockable_items) {
                                let shippingMethods = addressResponse.data.data?.shippingMethods || addressResponse.data?.shippingMethods;
                                let selectedRate = null;

                                if (shippingMethods) {
                                    for (let carrier in shippingMethods) {
                                        if (shippingMethods[carrier].rates && shippingMethods[carrier].rates.length > 0) {
                                            selectedRate = shippingMethods[carrier].rates[0].method;
                                            break;
                                        }
                                    }
                                }

                                if (! selectedRate) {
                                    selectedRate = 'flatrate_flatrate';
                                }

                                await this.$axios.post('{{ route('shop.checkout.onepage.shipping_methods.store') }}', {
                                    shipping_method: selectedRate,
                                });
                            }

                            // 3. Save selected payment method
                            let paymentMethod = this.selectedPaymentMethod;
                            if (! paymentMethod && this.paymentMethods && this.paymentMethods.length > 0) {
                                paymentMethod = this.paymentMethods[0].method || this.paymentMethods[0].payment;
                            }

                            if (paymentMethod) {
                                await this.$axios.post('{{ route('shop.checkout.onepage.payment_methods.store') }}', {
                                    payment: paymentMethod,
                                });
                            }

                            // 4. Create Order
                            let orderResponse = await this.$axios.post('{{ route('shop.checkout.onepage.orders.store') }}');

                            if (orderResponse.data.data?.redirect) {
                                window.location.href = orderResponse.data.data.redirect_url;
                            } else {
                                window.location.href = '{{ route('shop.checkout.onepage.success') }}';
                            }
                        } catch (error) {
                            this.isPlacingOrder = false;

                            if (error.response?.status === 422 && setErrors && error.response.data?.errors) {
                                setErrors(error.response.data.errors);
                                setTimeout(() => {
                                    const firstError = document.querySelector('.text-red-500, [aria-invalid="true"]');
                                    if (firstError) {
                                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 50);
                            } else {
                                let message = error.response?.data?.message || 'حدث خطأ أثناء معالجة الطلب، يرجى المحاولة مرة أخرى.';
                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            }
                        }
                    },

                    pushBeginCheckout(cart) {
                        if (!cart || !cart.items || cart.items.length === 0) return;
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({ ecommerce: null });
                        
                        let items = cart.items.map((item, index) => {
                            let price = item.formatted_price ? parseFloat(item.formatted_price.replace(/[^0-9.]/g, '')) : 0;
                            return {
                                item_id: item.sku || item.product_url_key || item.id,
                                item_name: item.name,
                                price: price,
                                quantity: item.quantity,
                                index: index
                            };
                        });
                        
                        let total = cart.formatted_grand_total ? parseFloat(cart.formatted_grand_total.replace(/[^0-9.]/g, '')) : 0;
                        
                        window.dataLayer.push({
                            event: 'begin_checkout',
                            ecommerce: {
                                currency: '{{ core()->getCurrentCurrencyCode() }}',
                                value: total,
                                items: items
                            }
                        });
                    }
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>
