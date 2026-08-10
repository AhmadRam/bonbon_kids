<!-- Header -->
<div class="flex justify-between items-center max-md:py-4 border-b border-zinc-200 pb-4 max-md:pb-3 max-md:border-none">
    <h1 class="text-2xl font-medium max-md:text-base">
        @lang('shop::app.checkout.onepage.summary.cart-summary')
    </h1>
    <a href="{{ route('shop.checkout.cart.index') }}" class="text-sm font-semibold text-toyBlue hover:underline hover:opacity-80 transition-all flex items-center gap-1">
        تعديل السلة
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
    </a>
</div>

<!-- Cart Items -->
<div class="mt-10 grid border-b border-zinc-200 max-md:mt-3 max-sm:mt-0">
    <div
        class="flex gap-x-4 pb-5 max-md:gap-x-3 max-md:pb-4"
        v-for="item in cart.items"
    >
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_image.before') !!}

        <img
            class="h-[90px] max-h-[90px] w-[90px] max-w-[90px] rounded-xl max-md:h-20 max-md:max-h-20 max-md:max-w-20 max-md:rounded-lg"
            :src="item.base_image.small_image_url"
            :alt="item.name"
            width="110"
            height="110"
        />

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_image.after') !!}

        <div>
            {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_name.before') !!}

            <p class="text-base text-navyBlue max-md:text-sm max-md:font-medium">
                @{{ item.name }}
            </p>

            {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_name.after') !!}

            <p class="mt-2.5 flex flex-col text-lg font-medium max-md:mt-1 max-md:text-base max-md:font-normal max-sm:text-sm">
                <template v-if="displayTax.prices == 'including_tax'">
                    @lang('shop::app.checkout.onepage.summary.price_and_qty', ['price' => '@{{ item.formatted_price_incl_tax }}', 'qty' => '@{{ item.quantity }}'])
                </template>

                <template v-else-if="displayTax.prices == 'both'">
                    @lang('shop::app.checkout.onepage.summary.price_and_qty', ['price' => '@{{ item.formatted_price_incl_tax }}', 'qty' => '@{{ item.quantity }}'])

                    <span class="text-xs font-normal">
                        @lang('shop::app.checkout.onepage.summary.excl-tax')

                        <span class="font-medium">@{{ item.formatted_total }}</span>
                    </span>
                </template>

                <template v-else>
                    @lang('shop::app.checkout.onepage.summary.price_and_qty', ['price' => '@{{ item.formatted_price }}', 'qty' => '@{{ item.quantity }}'])
                </template>
            </p>
        </div>
    </div>
</div>

<!-- Cart Totals -->
<div class="mb-8 mt-6 grid gap-4 max-md:mb-0 max-sm:mt-4 max-sm:gap-2.5">
    <!-- Sub Total -->
    {!! view_render_event('bagisto.shop.checkout.onepage.summary.sub_total.before') !!}

    <template v-if="displayTax.subtotal == 'including_tax'">
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.sub-total')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_sub_total_incl_tax }}
            </p>
        </div>
    </template>

    <template v-else-if="displayTax.subtotal == 'both'">
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.sub-total-excl-tax')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_sub_total }}
            </p>
        </div>
        
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.sub-total-incl-tax')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_sub_total_incl_tax }}
            </p>
        </div>
    </template>

    <template v-else>
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.sub-total')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_sub_total }}
            </p>
        </div>
    </template>

    {!! view_render_event('bagisto.shop.checkout.onepage.summary.sub_total.after') !!}

    <!-- Discount -->
    {!! view_render_event('bagisto.shop.checkout.onepage.summary.discount_amount.before') !!}

    <div
        class="flex justify-between text-right"
        v-if="cart.discount_amount && parseFloat(cart.discount_amount) > 0"
    >
        <p class="text-base max-sm:text-sm">
            @lang('shop::app.checkout.onepage.summary.discount-amount')
        </p>

        <p class="text-base font-medium max-sm:text-sm">
            @{{ cart.formatted_discount_amount }}
        </p>
    </div>

    {!! view_render_event('bagisto.shop.checkout.onepage.summary.discount_amount.after') !!}

    <!-- Apply Coupon -->
    {!! view_render_event('bagisto.shop.checkout.onepage.summary.coupon.before') !!}

    @include('shop::checkout.coupon')

    {!! view_render_event('bagisto.shop.checkout.onepage.summary.coupon.after') !!}

    <!-- Shipping Rates -->
    {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.before') !!}
        
    <template v-if="displayTax.shipping == 'including_tax'">
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.delivery-charges')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_shipping_amount_incl_tax }}
            </p>
        </div>
    </template>

    <template v-else-if="displayTax.shipping == 'both'">
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.delivery-charges-excl-tax')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_shipping_amount }}
            </p>
        </div>
        
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.delivery-charges-incl-tax')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_shipping_amount_incl_tax }}
            </p>
        </div>
    </template>

    <template v-else>
        <div class="flex justify-between text-right">
            <p class="text-base max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.delivery-charges')
            </p>

            <p class="text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_shipping_amount }}
            </p>
        </div>
    </template>

    {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.after') !!}


    {{--
    <!-- Taxes -->
    {!! view_render_event('bagisto.shop.checkout.onepage.summary.tax.before') !!}

    <div
        class="flex justify-between text-right"
        v-if="! cart.tax_total"
    >
        <p class="text-base max-md:font-normal max-sm:text-sm">
            @lang('shop::app.checkout.onepage.summary.tax')
        </p>

        <p class="text-lg font-semibold max-sm:text-sm">
            @{{ cart.formatted_tax_total }}
        </p>
    </div>

    <div
        class="flex flex-col gap-2 border-y py-2"
        v-else
    >
        <div
            class="flex cursor-pointer justify-between text-right"
            @click="cart.show_taxes = ! cart.show_taxes"
        >
            <p class="text-base max-md:font-normal max-sm:text-sm">
                @lang('shop::app.checkout.onepage.summary.tax')
            </p>

            <p class="flex items-center gap-1 text-base font-medium max-sm:text-sm">
                @{{ cart.formatted_tax_total }}
                
                <span
                    class="text-xl"
                    :class="{'icon-arrow-up': cart.show_taxes, 'icon-arrow-down': ! cart.show_taxes}"
                ></span>
            </p>
        </div>

        <div
            class="flex flex-col gap-1"
            v-show="cart.show_taxes"
        >
            <div
                class="flex justify-between gap-1 text-right"
                v-for="(amount, index) in cart.applied_taxes"
            >
                <p class="text-sm max-md:font-normal">
                    @{{ index }}
                </p>

                <p class="text-sm font-medium">
                    @{{ amount }}
                </p>
            </div>
        </div>
    </div>

    {!! view_render_event('bagisto.shop.checkout.onepage.summary.tax.after') !!}
    --}}

    <!-- Cart Grand Total -->
    {!! view_render_event('bagisto.shop.checkout.onepage.summary.grand_total.before') !!}

    <div class="flex justify-between text-right">
        <p class="text-lg font-semibold max-sm:text-sm">
            @lang('shop::app.checkout.onepage.summary.grand-total')
        </p>

        <p class="text-lg font-semibold max-sm:text-sm">
            @{{ cart.formatted_grand_total }}
        </p>
    </div>

    {!! view_render_event('bagisto.shop.checkout.onepage.summary.grand_total.after') !!}

    <!-- Free Gift Wrapping Notice -->
    <div class="mt-4 rounded-xl bg-[#F0FDF4] p-4 border border-[#BBF7D0] text-right shadow-sm flex flex-col gap-3">
        <div class="flex items-start gap-2">
            <span class="text-xl">🎁</span>
            <p class="text-sm text-[#166534] leading-relaxed">
                <strong>@lang('shop::app.checkout.onepage.summary.free-gift-wrapping')</strong><br>
                @lang('shop::app.checkout.onepage.summary.gift-wrapping-desc')
            </p>
        </div>
        <a href="https://wa.me/+96592214430?text={{ rawurlencode(trans('shop::app.checkout.onepage.summary.whatsapp-msg')) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#25D366] px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-[#128C7E] shadow-sm mt-1 w-full sm:w-auto sm:self-end">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 448 512" fill="currentColor">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.6-12 1.9-5.5-2.8-23.4-8.6-44.6-27.6-16.5-14.8-27.6-33.1-30.9-38.6-3.2-5.6-.3-8.6 2.4-11.4 2.5-2.5 5.5-6.5 8.3-9.7 2.7-3.2 3.7-5.6 5.5-9.3 1.8-3.7 .9-7-4-10.2-2.8-3.2-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3s19.9 53.7 22.7 57.4c2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
            </svg>
            @lang('shop::app.checkout.onepage.summary.contact-whatsapp')
        </a>
    </div>
</div>
