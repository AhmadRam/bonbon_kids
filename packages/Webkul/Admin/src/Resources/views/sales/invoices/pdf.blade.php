@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa']);
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">

<head>
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@lang('shop::app.customers.account.orders.invoice-pdf.invoice') #{{ $invoice->increment_id ?? $invoice->id }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style type="text/css">
        * {
            font-family: 'Cairo', 'DejaVu Sans', 'Segoe UI', Tahoma, sans-serif;
            box-sizing: border-box;
        }

        body,
        th,
        td,
        h5 {
            font-size: 13px;
            color: #000;
        }

        body {
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
            max-width: 960px;
            margin: 0 auto;
            display: block;
        }

        .invoice-summary {
            margin-bottom: 20px;
        }

        .table {
            margin: 20px 0px 0px 0px;
            border-spacing: 0px;
        }

        .table table {
            width: 100%;
            border-collapse: collapse;
            text-align: start;
            table-layout: fixed;
        }

        .table thead th {
            font-weight: 700;
            border-top: solid 1px #d3d3d3;
            border-bottom: solid 1px #d3d3d3;
            border-left: solid 1px #d3d3d3;
            padding: 8px 12px;
            background: #005aff0d;
        }

        .table thead th:last-child {
            border-right: solid 1px #d3d3d3;
        }

        .table tbody td {
            padding: 8px 10px;
            color: #222;
            vertical-align: top;
            border-bottom: solid 1px #d3d3d3;
            border-left: solid 1px #d3d3d3;
        }

        .table tbody td:last-child {
            border-right: solid 1px #d3d3d3;
        }

        .table tbody td p,
        p {
            margin: 0 0 4px 0;
            color: #000;
        }

        .sale-summary {
            margin-top: 20px;
            float: {{ $isRtl ? 'left' : 'right' }};
            background-color: #005aff0d;
            border: 1px solid #d3d3d3;
            border-radius: 4px;
            min-width: 320px;
        }

        .sale-summary tr td {
            padding: 5px 12px;
        }

        .sale-summary tr.bold {
            font-weight: 700;
        }

        .label {
            color: #000;
            font-weight: bold;
        }

        .merchant-details {
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .merchant-details-title {
            font-weight: bold;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .align-start {
            text-align: start;
        }

        .col-6 {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .table-header {
            color: #0041FF;
            text-align: start;
        }

        .header {
            padding: 10px 0px 20px 0px;
            width: 100%;
            position: relative;
            border-bottom: solid 2px #0041FF;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .row {
            display: flex;
            justify-content: space-between;
        }

        .item-options {
            font-size: 11px;
            color: #555;
            margin-top: 3px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }

            @page {
                size: A4;
                margin: 10mm 15mm;
            }
        }
    </style>
</head>

<body style="background-image: none; background-color: #fff;">
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
        } else {
            $logoSrc = asset('themes/shop/default/build/assets/logo-DiAkDw2e.svg');
        }

        $storeName = core()->getConfigData('sales.shipping.origin.store_name') ?: (app()->getLocale() == 'ar' ? 'متجر بونبون للألعاب' : 'BonBon Toys Store');
    @endphp

    <!-- Floating Print Button (Hidden in print) -->
    <div class="no-print" style="position: fixed; top: 15px; {{ $isRtl ? 'left: 20px;' : 'right: 20px;' }} z-index: 9999; display: flex; gap: 10px;">
        <button onclick="window.print();" style="background: #0041FF; color: #fff; border: none; padding: 10px 22px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,65,255,0.3); display: flex; align-items: center; gap: 8px;">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>{{ app()->getLocale() == 'ar' ? 'طباعة الفاتورة' : 'Print Invoice' }}</span>
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <!-- Left Logo -->
            <div class="image">
                <img style="max-height: 80px; width: auto;" src="{{ $logoSrc }}" alt="Bonbon Logo" />
            </div>

            <!-- Invoice Title in the Middle -->
            <div style="text-align: center;">
                <span style="font-size: 30px; color: #0041FF; font-weight: 800; letter-spacing: 1px;">
                    {{ strtoupper(__('shop::app.customers.account.orders.invoice-pdf.invoice')) }}
                </span><br>
                <span style="color: #111; font-size: 17px; font-weight: 700; margin-top: 4px; display: inline-block;">
                    {{ $storeName }}
                </span>
            </div>

            <!-- Right Logo -->
            <div class="image">
                <img style="max-height: 80px; width: auto;" src="{{ $logoSrc }}" alt="Bonbon Logo" />
            </div>
        </div>

        <!-- Order & Merchant Information -->
        <div class="row" style="padding: 15px 0px;">
            <!-- Left Details -->
            <div class="col-6">
                <div class="merchant-details">
                    <div>
                        <span class="label">@lang('shop::app.customers.account.orders.invoice-pdf.invoice-id'): </span>
                        <span class="value">#{{ $invoice->increment_id ?? $invoice->id }}</span>
                    </div>

                    <div>
                        <span class="label">@lang('shop::app.customers.account.orders.invoice-pdf.date'): </span>
                        <span class="value">{{ core()->formatDate($invoice->created_at, 'd-m-Y') }}</span>
                    </div>

                    @if (core()->getConfigData('sales.shipping.origin.store_name'))
                        <div style="padding-top: 10px;">
                            <span class="merchant-details-title">{{ core()->getConfigData('sales.shipping.origin.store_name') }}</span>
                        </div>
                    @endif

                    @if (core()->getConfigData('sales.shipping.origin.address1'))
                        <div>{{ core()->getConfigData('sales.shipping.origin.address1') }}</div>
                    @endif

                    @if (core()->getConfigData('sales.shipping.origin.city') || core()->getConfigData('sales.shipping.origin.country'))
                        <div>
                            <span>{{ core()->getConfigData('sales.shipping.origin.city') }}</span>
                            @if (core()->getConfigData('sales.shipping.origin.country'))
                                <span>{{ core()->country_name(core()->getConfigData('sales.shipping.origin.country')) }}</span>
                            @endif
                        </div>
                    @endif

                    @if (core()->getConfigData('sales.shipping.origin.contact'))
                        <div>
                            <span class="merchant-details-title">@lang('shop::app.customers.account.orders.invoice-pdf.contact-number'): </span>
                            {{ core()->getConfigData('sales.shipping.origin.contact') }}
                        </div>
                    @endif

                    @if (core()->getConfigData('sales.shipping.origin.vat_number'))
                        <div>
                            <span class="merchant-details-title">@lang('shop::app.customers.account.orders.invoice-pdf.vat-number'): </span>
                            {{ core()->getConfigData('sales.shipping.origin.vat_number') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Details -->
            <div class="col-6" style="{{ $isRtl ? 'padding-right: 40px;' : 'padding-left: 40px;' }}">
                <div class="merchant-details">
                    <div>
                        <span class="label">@lang('shop::app.customers.account.orders.invoice-pdf.order-id'): </span>
                        <span class="value">#{{ $invoice->order->increment_id }}</span>
                    </div>

                    <div>
                        <span class="label">@lang('shop::app.customers.account.orders.invoice-pdf.order-date'): </span>
                        <span class="value">{{ core()->formatDate($invoice->order->created_at, 'd-m-Y') }}</span>
                    </div>

                    @if ($invoice->hasPaymentTerm())
                        <div>
                            <span class="label">@lang('shop::app.customers.account.orders.invoice-pdf.payment-terms'): </span>
                            <span class="value">{{ $invoice->getFormattedPaymentTerm() }}</span>
                        </div>
                    @endif

                    @if (core()->getConfigData('sales.shipping.origin.bank_details'))
                        <div style="padding-top: 10px;">
                            <span class="merchant-details-title">@lang('shop::app.customers.account.orders.invoice-pdf.bank-details'):</span>
                            <div>{{ core()->getConfigData('sales.shipping.origin.bank_details') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="invoice-summary">
            <!-- Billing & Shipping Address Details -->
            <div class="table address">
                <table>
                    <thead>
                        <tr>
                            <th class="table-header" style="width: 50%;">
                                {{ ucwords(trans('shop::app.customers.account.orders.invoice-pdf.bill-to')) }}
                            </th>

                            @if ($invoice->order->shipping_address)
                                <th class="table-header" style="width: 50%;">
                                    {{ ucwords(trans('shop::app.customers.account.orders.invoice-pdf.ship-to')) }}
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            @foreach (['billing_address', 'shipping_address'] as $addressType)
                                @if ($invoice->order->$addressType)
                                    <td>
                                        @if (!empty($invoice->order->$addressType->company_name))
                                            <p><strong>{{ $invoice->order->$addressType->company_name }}</strong></p>
                                        @endif

                                        <p><strong>{{ $invoice->order->$addressType->name }}</strong></p>

                                        <p>
                                            @php
                                                $rawAddress = $invoice->order->$addressType->address;
                                                $addressLines = preg_split('/\r\n|\r|\n/', trim($rawAddress));
                                            @endphp

                                            @if ($invoice->order->$addressType->country == 'KW' && count($addressLines) > 1)
                                                @if (isset($addressLines[0]) && trim($addressLines[0]) !== '' && $addressLines[0] !== '0')
                                                    <span>{{ app()->getLocale() == 'ar' ? 'القطعة' : 'Block' }}: {{ $addressLines[0] }}</span><br>
                                                @endif
                                                @if (isset($addressLines[1]) && trim($addressLines[1]) !== '' && $addressLines[1] !== '0')
                                                    <span>{{ app()->getLocale() == 'ar' ? 'الشارع' : 'Street' }}: {{ $addressLines[1] }}</span><br>
                                                @endif
                                                @if (isset($addressLines[2]) && trim($addressLines[2]) !== '' && $addressLines[2] !== '0')
                                                    <span>{{ app()->getLocale() == 'ar' ? 'المنزل' : 'House' }}: {{ $addressLines[2] }}{{ (isset($addressLines[3]) && trim($addressLines[3]) !== '' && $addressLines[3] !== 'NA') ? ' / ' . (app()->getLocale() == 'ar' ? 'الدور' : 'Floor') . ': ' . $addressLines[3] : '' }}{{ (isset($addressLines[4]) && trim($addressLines[4]) !== '' && $addressLines[4] !== 'NA') ? ' / ' . (app()->getLocale() == 'ar' ? 'الشقة' : 'Flat') . ': ' . $addressLines[4] : '' }}</span><br>
                                                @endif
                                                @if (isset($addressLines[5]) && trim($addressLines[5]) !== '' && $addressLines[5] !== 'NA')
                                                    <span>{{ app()->getLocale() == 'ar' ? 'الجادة' : 'Avenue' }}: {{ $addressLines[5] }}</span><br>
                                                @endif
                                            @else
                                                {!! nl2br(e($rawAddress)) !!}<br>
                                            @endif
                                        </p>

                                        @if (!empty($invoice->order->$addressType->postcode) || !empty($invoice->order->$addressType->city))
                                            <p>{{ trim(($invoice->order->$addressType->postcode ?? '') . ' ' . ($invoice->order->$addressType->city ?? '')) }}</p>
                                        @endif

                                        @if (!empty($invoice->order->$addressType->state))
                                            <p>{{ $invoice->order->$addressType->state }}</p>
                                        @endif

                                        @if (!empty($invoice->order->$addressType->country))
                                            <p>{{ core()->country_name($invoice->order->$addressType->country) }}</p>
                                        @endif

                                        <p><strong>@lang('shop::app.customers.account.orders.invoice-pdf.contact'):</strong> {{ $invoice->order->$addressType->phone }}</p>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Payment & Shipping Methods -->
            <div class="table payment-shipment">
                <table>
                    <thead>
                        <tr>
                            <th class="table-header" style="width: 50%;">@lang('shop::app.customers.account.orders.invoice-pdf.payment-method')</th>

                            @if ($invoice->order->shipping_address)
                                <th class="table-header" style="width: 50%;">@lang('shop::app.customers.account.orders.invoice-pdf.shipping-method')</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                {{ core()->getConfigData('sales.payment_methods.' . $invoice->order->payment->method . '.title') ?? $invoice->order->payment->method }}

                                @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($invoice->order->payment->method); @endphp

                                @if (!empty($additionalDetails))
                                    <div style="margin-top: 4px;">
                                        <label class="label">{{ $additionalDetails['title'] }}:</label>
                                        <span>{{ $additionalDetails['value'] }}</span>
                                    </div>
                                @endif
                            </td>

                            @if ($invoice->order->shipping_address)
                                <td>
                                    {{ $invoice->order->shipping_title }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Products Table -->
            <div class="table items">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center table-header" style="width: 15%;">@lang('shop::app.customers.account.orders.invoice-pdf.sku')</th>
                            <th class="align-start table-header" style="width: 35%;">@lang('shop::app.customers.account.orders.invoice-pdf.product-name')</th>
                            <th class="text-center table-header" style="width: 12%;">@lang('shop::app.customers.account.orders.invoice-pdf.price')</th>
                            <th class="text-center table-header" style="width: 8%;">@lang('shop::app.customers.account.orders.invoice-pdf.qty')</th>
                            <th class="text-center table-header" style="width: 10%;">@lang('shop::app.customers.account.orders.invoice-pdf.subtotal')</th>
                            <th class="text-center table-header" style="width: 10%;">@lang('shop::app.customers.account.orders.invoice-pdf.discount')</th>
                            <th class="text-center table-header" style="width: 10%;">@lang('shop::app.customers.account.orders.invoice-pdf.grand-total')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="text-center">{{ $item->getTypeInstance()->getOrderedItem($item)->sku ?? $item->sku }}</td>

                                <td class="align-start">
                                    <span style="font-weight: 600;">{{ $item->name }}</span>

                                    @if (isset($item->additional['attributes']))
                                        <div class="item-options">
                                            @foreach ($item->additional['attributes'] as $attribute)
                                                <b>{{ $attribute['attribute_name'] }}: </b>{{ $attribute['option_label'] }}<br>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <td class="text-center">{!! core()->formatBasePrice($item->base_price, true) !!}</td>

                                <td class="text-center">{{ $item->qty }}</td>

                                <td class="text-center">{!! core()->formatBasePrice($item->base_total, true) !!}</td>

                                <td class="text-center">{!! core()->formatBasePrice($item->base_discount_amount, true) !!}</td>

                                <td class="text-center" style="font-weight: bold;">{!! core()->formatBasePrice($item->base_total + $item->base_tax_amount - $item->base_discount_amount, true) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Sale Summary -->
            <table class="sale-summary">
                <tr>
                    <td>@lang('shop::app.customers.account.orders.invoice-pdf.subtotal')</td>
                    <td style="width: 10px;">-</td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">{!! core()->formatBasePrice($invoice->base_sub_total, true) !!}</td>
                </tr>

                @if ((float) $invoice->base_shipping_amount > 0)
                    <tr>
                        <td>@lang('shop::app.customers.account.orders.invoice-pdf.shipping-handling')</td>
                        <td>-</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">{!! core()->formatBasePrice($invoice->base_shipping_amount, true) !!}</td>
                    </tr>
                @endif

                @if ((float) $invoice->base_tax_amount > 0)
                    <tr>
                        <td>@lang('shop::app.customers.account.orders.invoice-pdf.tax')</td>
                        <td>-</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">{!! core()->formatBasePrice($invoice->base_tax_amount, true) !!}</td>
                    </tr>
                @endif

                @if ((float) $invoice->base_discount_amount > 0)
                    <tr>
                        <td>@lang('shop::app.customers.account.orders.invoice-pdf.discount')</td>
                        <td>-</td>
                        <td style="text-align: {{ $isRtl ? 'left' : 'right' }};">{!! core()->formatBasePrice($invoice->base_discount_amount, true) !!}</td>
                    </tr>
                @endif

                <tr>
                    <td colspan="3" style="padding: 0;">
                        <hr style="border: 0; border-top: 1px solid #d3d3d3; margin: 4px 0;">
                    </td>
                </tr>

                <tr style="font-weight: 700; font-size: 14px;">
                    <td style="color: #0041FF;">@lang('shop::app.customers.account.orders.invoice-pdf.grand-total')</td>
                    <td>-</td>
                    <td style="text-align: {{ $isRtl ? 'left' : 'right' }}; color: #0041FF;">{!! core()->formatBasePrice($invoice->order->base_grand_total, true) !!}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Automatic Print Trigger on Page Load -->
    <script type="text/javascript">
        window.onload = function () {
            window.print();
        };
    </script>
</body>

</html>
