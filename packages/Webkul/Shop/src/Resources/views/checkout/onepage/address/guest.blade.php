{!! view_render_event('bagisto.shop.checkout.onepage.address.guest.before') !!}

<!-- Guest Address Vue Component -->
<v-checkout-address-guest
    :cart="cart"
    @processing="stepForward"
    @processed="stepProcessed"
></v-checkout-address-guest>

{!! view_render_event('bagisto.shop.checkout.onepage.address.guest.after') !!}

@include('shop::checkout.onepage.address.form')

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-address-guest-template"
    >
        <!-- Address Form -->
        <x-shop::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, addAddress)">
                <!-- Guest Billing Address -->
                <div class="mb-4">
                    {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.billing.before') !!}

                    <!-- Billing Address Header -->
                    {{-- <div class="flex items-center justify-between">
                        <h2 class="text-xl font-medium max-md:text-lg max-sm:text-base">
                            @lang('shop::app.checkout.onepage.address.billing-address')
                        </h2>
                    </div> --}}
                
                    <!-- Billing Address Form -->
                    <v-checkout-address-form
                        control-name="billing"
                        :address="cart.billing_address || undefined"
                    ></v-checkout-address-form>

                    <!-- Use for Shipping Checkbox -->
                    {{-- <x-shop::form.control-group
                        class="!mb-0 flex items-center gap-2.5"
                        v-if="cart.have_stockable_items"
                    >
                        <x-shop::form.control-group.control
                            type="checkbox"
                            name="billing.use_for_shipping"
                            id="use_for_shipping"
                            for="use_for_shipping"
                            value="1"
                            @change="useBillingAddressForShipping = ! useBillingAddressForShipping"
                            ::checked="!! useBillingAddressForShipping"
                        />

                        <label
                            class="cursor-pointer select-none text-base text-zinc-500 max-md:text-sm max-sm:text-xs ltr:pl-0 rtl:pr-0"
                            for="use_for_shipping"
                        >
                            @lang('shop::app.checkout.onepage.address.same-as-billing')
                        </label>
                    </x-shop::form.control-group> --}}

                    {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.billing.after') !!}
                </div>

                <!-- Guest Shipping Address -->
                <template v-if="cart.have_stockable_items">
                    <div
                        class="mt-8"
                        v-if="! useBillingAddressForShipping"
                    >
                        {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.shipping.before') !!}

                        <!-- Shipping Address Header -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-medium max-md:text-lg max-sm:text-base">
                                @lang('shop::app.checkout.onepage.address.shipping-address')
                            </h2>
                        </div>
                    
                        <!-- Shipping Address Form -->
                        <v-checkout-address-form
                            control-name="shipping"
                            :address="cart.shipping_address || undefined"
                        ></v-checkout-address-form>

                        {!! view_render_event('bagisto.shop.checkout.onepage.address.guest.shipping.after') !!}
                    </div>
                </template>

                <!-- Hidden Submit Button for single-button checkout -->
                <button
                    type="submit"
                    id="checkout-guest-address-submit-btn"
                    class="hidden"
                ></button>
            </form>
        </x-shop::form>
    </script>

    <script type="module">
        app.component('v-checkout-address-guest', {
            template: '#v-checkout-address-guest-template',

            props: ['cart'],

            emits: ['processing', 'processed'],

            data() {
                return {
                    useBillingAddressForShipping: true,

                    isStoring: false,
                }
            },

            created() {
                this.useBillingAddressForShipping = true;
            },

            mounted() {
                this.$emitter.on('trigger-address-submit', () => {
                    const btn = document.getElementById('checkout-guest-address-submit-btn');
                    if (btn) {
                        btn.click();
                        setTimeout(() => {
                            const firstError = document.querySelector('.text-red-500, [aria-invalid="true"]');
                            if (firstError) {
                                this.$emitter.emit('address-validation-failed');
                                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 50);
                    }
                });
            },

            beforeUnmount() {
                this.$emitter.off('trigger-address-submit');
            },

            methods: {
                addAddress(params, { setErrors }) {
                    params['billing']['use_for_shipping'] = this.useBillingAddressForShipping;

                    this.$emitter.emit('address-validated-ready', {
                        params: params,
                        setErrors: setErrors,
                    });
                },

                moveToNextStep() {
                    if (this.cart.have_stockable_items) {
                        this.$emit('processing', 'shipping');
                    } else {
                        this.$emit('processing', 'payment');
                    }
                }
            }
        });
    </script>
@endPushOnce