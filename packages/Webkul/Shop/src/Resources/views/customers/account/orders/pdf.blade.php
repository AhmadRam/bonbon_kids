<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <meta
            http-equiv="Cache-control"
            content="no-cache"
        >

        <meta
            http-equiv="Content-Type"
            content="text/html; charset=utf-8"
        />

        @php
            $fontPath = [];

            if (app()->getLocale() == 'en' && $orderCurrencyCode == 'INR') {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            }  else {
                $fontFamily = [
                    'regular' => 'DejaVu Sans, Arial, sans-serif',
                    'bold'    => 'DejaVu Sans, Arial, sans-serif',
                ];
            }

            if (in_array(app()->getLocale(), ['ar', 'he', 'fa', 'tr', 'ru', 'uk'])) {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            } elseif (app()->getLocale() == 'zh_CN') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansSC-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansSC-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans SC',
                    'bold'    => 'Noto Sans SC Bold',
                ];
            } elseif (app()->getLocale() == 'ja') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansJP-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansJP-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans JP',
                    'bold'    => 'Noto Sans JP Bold',
                ];
            } elseif (app()->getLocale() == 'hi_IN') {
                $fontPath = [
                    'regular' => asset('fonts/Hind-Regular.ttf'),
                    'bold'    => asset('fonts/Hind-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Hind',
                    'bold'    => 'Hind Bold',
                ];
            } elseif (app()->getLocale() == 'bn') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansBengali-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansBengali-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans Bengali',
                    'bold'    => 'Noto Sans Bengali Bold',
                ];
            } elseif (app()->getLocale() == 'sin') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansSinhala-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansSinhala-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans Sinhala',
                    'bold'    => 'Noto Sans Sinhala Bold',
                ];
            }
        @endphp

        <!-- lang supports inclusion -->
        <style type="text/css">
            @if (! empty($fontPath['regular']))
                @font-face {
                    src: url({{ $fontPath['regular'] }}) format('truetype');
                    font-family: {{ $fontFamily['regular'] }};
                }
            @endif

            @if (! empty($fontPath['bold']))
                @font-face {
                    src: url({{ $fontPath['bold'] }}) format('truetype');
                    font-family: {{ $fontFamily['bold'] }};
                    font-style: bold;
                }
            @endif

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: {{ $fontFamily['regular'] }};
            }

            body {
                font-size: 10px;
                color: #091341;
                font-family: "{{ $fontFamily['regular'] }}";
            }

            b, th {
                font-family: "{{ $fontFamily['bold'] }}";
            }

            .page-content {
                padding: 12px;
            }

            .page-header {
                border-bottom: 1px solid #E9EFFC;
                text-align: center;
                font-size: 24px;
                text-transform: uppercase;
                color: #000DBB;
                padding: 24px 0 35px 0;
                margin: 0;
            }

            .logo-container {
                position: absolute;
                top: 10px;
                left: 20px;
            }

            .logo-container.rtl {
                left: auto;
                right: 20px;
            }

            .logo-container img {
                width: 110px;
                max-width: 110px;
                height: auto;
            }

            .page-header b {
                display: inline-block;
                vertical-align: middle;
            }

            .small-text {
                font-size: 7px;
            }

            table {
                width: 100%;
                border-spacing: 1px 0;
                border-collapse: separate;
                margin-bottom: 16px;
            }

            table thead th {
                background-color: #E9EFFC;
                color: #000DBB;
                padding: 6px 18px;
                text-align: left;
            }

            table.rtl thead tr th {
                text-align: right;
            }

            table tbody td {
                padding: 9px 18px;
                border-bottom: 1px solid #E9EFFC;
                text-align: left;
                vertical-align: top;
            }

            table.rtl tbody tr td {
                text-align: right;
            }

            .summary {
                width: 100%;
                display: inline-block;
            }

            .summary table {
                float: right;
                width: 250px;
                padding-top: 5px;
                padding-bottom: 5px;
                background-color: #E9EFFC;
                white-space: nowrap;
            }

            .summary table.rtl {
                width: 280px;
            }

            .summary table.rtl {
                margin-right: 480px;
            }

            .summary table td {
                padding: 5px 10px;
            }

            .summary table td:nth-child(2) {
                text-align: center;
            }

            .summary table td:nth-child(3) {
                text-align: right;
            }
        </style>
    </head>

    <body dir="{{ core()->getCurrentLocale()->direction }}">
        <div class="logo-container {{ core()->getCurrentLocale()->direction }}">
            @php
                $invoiceLogo = core()->getConfigData('sales.invoice_settings.pdf_print_outs.logo');
                $channelLogo = core()->getCurrentChannel()->logo;
                $logoSrc = null;

                if ($invoiceLogo && Storage::has($invoiceLogo)) {
                    $mime = Storage::mimeType($invoiceLogo) ?: 'image/png';
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(Storage::get($invoiceLogo));
                } elseif ($channelLogo && Storage::has($channelLogo)) {
                    $mime = Storage::mimeType($channelLogo) ?: 'image/png';
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(Storage::get($channelLogo));
                }
            @endphp

            @if ($logoSrc)
                <img src="{{ $logoSrc }}" width="110" style="width: 110px; height: auto;" alt="logo" />
            @else
                <img src="data:image/png;base64,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oaxlO71CpuNNSNLiOLIefh+8IiHQTQ1TyRNspxemhmv1EuwY5TuDNckuxvwc2nGnyLkqn12cAjN2H1Ib6sIi580ajm+tk7a9S3YmG6SbGmmgwAiD9FKT3clGiH5K3tkZ2hTJBRsB6lWcSR9BNzXCcgXwfK4pynEomo9HRT+rRrMGZBCAvdSwx9ZTcKNc1El8pTyI0UB/BL8a2NcnlLOzp0mDV4F8AwBFKttsFt+NwTvHr+B+x+N4d6hpbMcJsVIafI+MYQVFm1BpAFAheiR7SLkLHTwP2j2Mgh9aILuilQd2HvIrepKN479hIjwu72IBQJSECQlOJVsB/G5wqe23653cHkwA9kkdkyswpscgFO2nj88l2wVDbD3M2ktIcTPAb+vl+KWEjZI7oJriZKh8lg09QxtyBIJccSjzSCx3mgxCRUog5jR6fF7AzzPA9/RiLshr5V3BBGkfBfE+ABMgEhBPpoH+pQheDC9N9+veOveNodGLJ4U8HyKrGOBkhLIUgbaKll5iTKDsSMytTPCEpbgLkGfC1em876l3X6QynZ6pyss1whsAKEVeQQffpOI+fa115Ky9/L/rImgnbonqaRLzdAgaMCaMZKONh49EK+O9bgr4DJA+HzVF07MF8i5RngSykSkmQaHIG2dnHTBzO61CGAL0KLpDaBsYBI8iSJ+QH7M373N4xw3hrzt88EjdFTax9Px/NDlIb4jfOEzR2bkMUbg8VRwhQCiCKRq3mGJXeGQ6vbdF4oBdw23haYTdjJa8ESpLAShTmZJuugExP6X/lk8fyO9LvxYcxS7OF7ELJZQTEOgkArQAKIw9pGwjwTbEeE6U3080fCJaE/+znJvs/A50Ynd4TBCnb6HKm1V4BIAWAmnkR0odi7lLRX5kAZ9CMP5CcEn7gOxW+GXgnyaO1tbRbUy+fc9BkbnjD44Gd++cYDddRpUTxDhhAhPBLjHZrOO6Xd7fO2BhaH7/qHAmncWiZ/ck/xxGhQTwAwApFDMAlgKYALABhhMBbMuaWDtuCJ1DzeATSG8Pmyo8HgmPylLFdQcpm8PJuD1UsHygvve+MeVs2oShaQ1M5OdVPSRs66KwDXZ7B+u7F75pAHBkU5FqiAhAGiTo7rFftet0HMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHMdxHOfXgOuvv94fguM4v7aoPwLHcRzHcRzHcRzHcRzHcQ49/h+ysLomscMPIgAAAABJRU5ErkJggg==" alt="logo" width="110" style="width: 110px; height: auto;" />
            @endif
        </div>

        <div class="page">
            <!-- Header -->
            <div class="page-header">
                <b>@lang('shop::app.customers.account.orders.invoice-pdf.invoice')</b>
            </div>

            <div class="page-content">
                <!-- Invoice Information -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <tbody>
                        <tr>
                            @if (core()->getConfigData('sales.invoice_settings.pdf_print_outs.invoice_id'))
                                <td style="width: 50%; padding: 2px 18px;border:none;">
                                    <b>
                                        @lang('shop::app.customers.account.orders.invoice-pdf.invoice-id'):
                                    </b>

                                    <span>
                                        #{{ $invoice->increment_id ?? $invoice->id }}
                                    </span>
                                </td>
                            @endif

                            @if (core()->getConfigData('sales.invoice_settings.pdf_print_outs.order_id'))
                                <td style="width: 50%; padding: 2px 18px;border:none;">
                                    <b>
                                        @lang('shop::app.customers.account.orders.invoice-pdf.order-id'):
                                    </b>

                                    <span>
                                        #{{ $invoice->order->increment_id }}
                                    </span>
                                </td>
                            @endif
                        </tr>

                        <tr>
                            <td style="width: 50%; padding: 2px 18px;border:none;">
                                <b>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.date'):
                                </b>

                                <span>
                                    {{ core()->formatDate($invoice->created_at, 'd-m-Y') }}
                                </span>
                            </td>

                            <td style="width: 50%; padding: 2px 18px;border:none;">
                                <b>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.order-date'):
                                </b>

                                <span>
                                    {{ core()->formatDate($invoice->order->created_at, 'd-m-Y') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Invoice Information -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <tbody>
                        <tr>
                            @if (! empty(core()->getConfigData('sales.shipping.origin.country')))
                                <td style="width: 50%; padding: 2px 18px;border:none;">
                                    <b style="display: inline-block; margin-bottom: 4px;">
                                        {{ core()->getConfigData('sales.shipping.origin.store_name') }}
                                    </b>

                                    <div>
                                        <div>{{ core()->getConfigData('sales.shipping.origin.address') }}</div>

                                        <div>{{ core()->getConfigData('sales.shipping.origin.zipcode') . ' ' . core()->getConfigData('sales.shipping.origin.city') }}</div>

                                        <div>{{ core()->getConfigData('sales.shipping.origin.state') . ', ' . core()->getConfigData('sales.shipping.origin.country') }}</div>
                                    </div>
                                </td>
                            @endif

                            <td style="width: 50%; padding: 2px 18px;border:none;">
                                @if ($invoice->hasPaymentTerm())
                                    <div style="margin-bottom: 12px">
                                        <b style="display: inline-block; margin-bottom: 4px;">
                                            @lang('shop::app.customers.account.orders.invoice-pdf.payment-terms'):
                                        </b>

                                        <span>
                                            {{ $invoice->getFormattedPaymentTerm() }}
                                        </span>
                                    </div>
                                @endif

                                @if (core()->getConfigData('sales.shipping.origin.bank_details'))
                                    <div>
                                        <b style="display: inline-block; margin-bottom: 4px;">
                                            @lang('shop::app.customers.account.orders.invoice-pdf.bank-details'):
                                        </b>

                                        <div>
                                            {!! nl2br(core()->getConfigData('sales.shipping.origin.bank_details')) !!}
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Billing & Shipping Address -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <thead>
                        <tr>
                            @if ($invoice->order->billing_address)
                                <th style="width: 50%;">
                                    <b>
                                        @lang('shop::app.customers.account.orders.invoice-pdf.bill-to')
                                    </b>
                                </th>
                            @endif

                            @if ($invoice->order->shipping_address)
                                <th style="width: 50%">
                                    <b>
                                        @lang('shop::app.customers.account.orders.invoice-pdf.ship-to')
                                    </b>
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            @if ($invoice->order->billing_address)
                                <td style="width: 50%">
                                    <div>{{ $invoice->order->billing_address->company_name ?? '' }}<div>

                                    <div>{{ $invoice->order->billing_address->name }}</div>

                                    <div>{{ $invoice->order->billing_address->address }}</div>

                                    <div>{{ $invoice->order->billing_address->postcode . ' ' . $invoice->order->billing_address->city }}</div>

                                    <div>{{ $invoice->order->billing_address->state . ', ' . core()->country_name($invoice->order->billing_address->country) }}</div>

                                    <div>@lang('shop::app.customers.account.orders.invoice-pdf.contact'): {{ $invoice->order->billing_address->phone }}</div>
                                </td>
                            @endif

                            @if ($invoice->order->shipping_address)
                                <td style="width: 50%">
                                    <div>{{ $invoice->order->shipping_address->company_name ?? '' }}<div>

                                    <div>{{ $invoice->order->shipping_address->name }}</div>

                                    <div>{{ $invoice->order->shipping_address->address }}</div>

                                    <div>{{ $invoice->order->shipping_address->postcode . ' ' . $invoice->order->shipping_address->city }}</div>

                                    <div>{{ $invoice->order->shipping_address->state . ', ' . core()->country_name($invoice->order->shipping_address->country) }}</div>

                                    <div>@lang('shop::app.customers.account.orders.invoice-pdf.contact'): {{ $invoice->order->shipping_address->phone }}</div>
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>

                <!-- Payment & Shipping Methods -->
                <table class="{{ core()->getCurrentLocale()->direction }}">
                    <thead>
                        <tr>
                            <th style="width: 50%">
                                <b>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.payment-method')
                                </b>
                            </th>

                            @if ($invoice->order->shipping_address)
                                <th style="width: 50%">
                                    <b>
                                        @lang('shop::app.customers.account.orders.invoice-pdf.shipping-method')
                                    </b>
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td style="width: 50%">
                                {{ core()->getConfigData('sales.payment_methods.' . $invoice->order->payment->method . '.title') }}

                                @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($invoice->order->payment->method); @endphp

                                @if (! empty($additionalDetails))
                                    <div class="row small-text">
                                        <span>{{ $additionalDetails['title'] }}:</span>

                                        <span>{{ $additionalDetails['value'] }}</span>
                                    </div>
                                @endif
                            </td>

                            @if ($invoice->order->shipping_address)
                                <td style="width: 50%">
                                    {{ $invoice->order->shipping_title }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>

                <!-- Items -->
                <div class="items">
                    <table class="{{ core()->getCurrentLocale()->direction }}">
                        <thead>
                            <tr>
                                <th>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.sku')
                                </th>

                                <th>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.product-name')
                                </th>

                                <th>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.price')
                                </th>

                                <th>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.qty')
                                </th>

                                <th>
                                    @lang('shop::app.customers.account.orders.invoice-pdf.subtotal')
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->getTypeInstance()->getOrderedItem($item)->sku }}
                                    </td>

                                    <td>
                                        {{ $item->name }}

                                        @if (isset($item->additional['attributes']))
                                            <div>
                                                @foreach ($item->additional['attributes'] as $attribute)
                                                    @if (
                                                        ! isset($attribute['attribute_type'])
                                                        || $attribute['attribute_type'] !== 'file'
                                                    )
                                                        <b>{{ $attribute['attribute_name'] }} : </b>{{ $attribute['option_label'] }}<br>
                                                    @else
                                                        {{ $attribute['attribute_name'] }} :

                                                        <a
                                                            href="{{ Storage::url($attribute['option_label']) }}"
                                                            class="text-blue-600 hover:underline"
                                                            download="{{ File::basename($attribute['option_label']) }}"
                                                        >
                                                            {{ File::basename($attribute['option_label']) }}
                                                        </a>

                                                        <br>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                            {!! core()->formatPrice($item->price_incl_tax, $orderCurrencyCode) !!}
                                        @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                                            {!! core()->formatPrice($item->price_incl_tax, $orderCurrencyCode) !!}

                                            <div class="small-text">
                                                @lang('shop::app.customers.account.orders.invoice-pdf.excl-tax')

                                                <span>
                                                    {{ core()->formatPrice($item->price, $orderCurrencyCode) }}
                                                </span>
                                            </div>
                                        @else
                                            {!! core()->formatPrice($item->price, $orderCurrencyCode) !!}
                                        @endif
                                    </td>

                                    <td>
                                        {{ $item->qty }}
                                    </td>

                                    <td>
                                        @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                            {!! core()->formatPrice($item->total_incl_tax, $orderCurrencyCode) !!}
                                        @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                                            {!! core()->formatPrice($item->total_incl_tax, $orderCurrencyCode) !!}

                                            <div class="small-text">
                                                @lang('shop::app.customers.account.orders.invoice-pdf.excl-tax')

                                                <span>
                                                    {{ core()->formatPrice($item->total, $orderCurrencyCode) }}
                                                </span>
                                            </div>
                                        @else
                                            {!! core()->formatPrice($item->total, $orderCurrencyCode) !!}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Table -->
                <div class="summary">
                    <table class="{{ core()->getCurrentLocale()->direction }}">
                        <tbody>
                            @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.subtotal')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->sub_total_incl_tax, $orderCurrencyCode) !!}</td>
                                </tr>
                            @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.subtotal-incl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->sub_total_incl_tax, $orderCurrencyCode) !!}</td>
                                </tr>

                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.subtotal-excl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->sub_total, $orderCurrencyCode) !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.subtotal')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->sub_total, $orderCurrencyCode) !!}</td>
                                </tr>
                            @endif

                            @if (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'including_tax')
                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.shipping-handling')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->shipping_amount_incl_tax, $orderCurrencyCode) !!}</td>
                                </tr>
                            @elseif (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'both')
                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.shipping-handling-incl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->shipping_amount_incl_tax, $orderCurrencyCode) !!}</td>
                                </tr>

                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.shipping-handling-excl-tax')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->shipping_amount, $orderCurrencyCode) !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.shipping-handling')</td>
                                    <td>-</td>
                                    <td>{!! core()->formatPrice($invoice->shipping_amount, $orderCurrencyCode) !!}</td>
                                </tr>
                            @endif

                            <tr>
                                <td>@lang('shop::app.customers.account.orders.invoice-pdf.tax')</td>
                                <td>-</td>
                                <td>{!! core()->formatPrice($invoice->tax_amount, $orderCurrencyCode) !!}</td>
                            </tr>

                            <tr>
                                <td>@lang('shop::app.customers.account.orders.invoice-pdf.discount')</td>
                                <td>-</td>
                                <td>{!! core()->formatPrice($invoice->discount_amount, $orderCurrencyCode) !!}</td>
                            </tr>

                            <tr>
                                <td style="border-top: 1px solid #FFFFFF;">
                                    <b>@lang('shop::app.customers.account.orders.invoice-pdf.grand-total')</b>
                                </td>
                                <td style="border-top: 1px solid #FFFFFF;">-</td>
                                <td style="border-top: 1px solid #FFFFFF;">
                                    <b>{!! core()->formatPrice($invoice->grand_total, $orderCurrencyCode) !!}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
