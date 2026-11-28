@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa']);
@endphp
<!DOCTYPE html>
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-control" content="no-cache">
    <title>@lang('shop::app.customers.account.orders.invoice-pdf.invoice') #{{ $invoice->increment_id ?? $invoice->id }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style type="text/css">
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            font-family: 'Cairo', 'DejaVu Sans', 'Segoe UI', Tahoma, sans-serif;
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            background-color: #f3f4f6;
            -webkit-text-size-adjust: 100%;
        }

        body,
        th,
        td,
        h5 {
            font-size: 12px;
            color: #000;
            line-height: 1.45;
        }

        .container {
            width: 210mm;
            max-width: 100%;
            margin: 16px auto;
            padding: 10mm;
            background-color: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: block;
        }

        .invoice-summary {
            margin-bottom: 16px;
        }

        .table {
            margin: 14px 0 0 0;
            width: 100%;
            max-width: 100%;
            border-spacing: 0px;
        }

        .table table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            text-align: start;
            table-layout: fixed;
            border: 1px solid #d3d3d3;
        }

        .table thead {
            display: table-header-group;
        }

        .table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .table thead th {
            font-weight: 700;
            border: 1px solid #d3d3d3;
            padding: 7px 10px;
            background: #005aff0d;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .table tbody td {
            padding: 7px 10px;
            color: #222;
            vertical-align: top;
            border: 1px solid #d3d3d3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .table.items thead th,
        .table.items tbody td {
            padding: 6px 5px;
            font-size: 11px;
        }

        .table.items tbody td:first-child {
            overflow-wrap: anywhere;
        }

        .table tbody td p,
        p {
            margin: 0 0 4px 0;
            color: #000;
        }

        .sale-summary-wrapper {
            display: flex;
            justify-content: flex-end;
            width: 100%;
            margin-top: 16px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .sale-summary {
            width: 280px;
            max-width: 100%;
            border-collapse: collapse;
            background-color: #005aff0d;
            border: 1px solid #d3d3d3;
            border-radius: 4px;
        }

        .sale-summary tr td {
            padding: 5px 10px;
            font-size: 12px;
        }

        .sale-summary tr td:last-child {
            white-space: nowrap;
            text-align: {{ $isRtl ? 'left' : 'right' }};
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

        .table-header {
            color: #0041FF;
            text-align: start;
        }

        .header {
            padding: 4px 0 14px 0;
            width: 100%;
            position: relative;
            border-bottom: solid 2px #0041FF;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            direction: ltr;
        }

        .header .image {
            flex: 0 0 auto;
            max-width: 28%;
            min-width: 0;
            display: flex;
            align-items: center;
        }

        .header .image img {
            max-height: 64px;
            max-width: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .header .header-title {
            flex: 1 1 auto;
            min-width: 0;
            text-align: center;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            width: 100%;
            padding: 12px 0;
        }

        .col-6 {
            flex: 1 1 50%;
            width: 50%;
            min-width: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
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

            html,
            body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .table,
            .table table {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>

<body>
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

        $companyLogoPath = public_path('images/company-logo.png');
        $companyLogoSrc = null;
        if (file_exists($companyLogoPath)) {
            $companyLogoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($companyLogoPath));
        } else {
            $companyLogoSrc = asset('images/company-logo.png');
        }

        $storeName = core()->getConfigData('sales.shipping.origin.store_name') ?: (app()->getLocale() == 'ar' ? 'شركة عمر خالد الشراح' : 'Omar Khalid Al-Sharrah Company');
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
                <img src="{{ $logoSrc }}" alt="Bonbon Logo" />
            </div>

            <!-- Invoice Title in the Middle -->
            <div class="header-title">
                <span style="font-size: 24px; color: #0041FF; font-weight: 800; letter-spacing: 1px;">
                    {{ strtoupper(__('shop::app.customers.account.orders.invoice-pdf.invoice')) }}
                </span><br>
                <span style="color: #111; font-size: 15px; font-weight: 700; margin-top: 4px; display: inline-block;">
                    {{ $storeName }}
                </span>
            </div>

            <!-- Right Logo -->
            <div class="image">
                <img src="{{ $companyLogoSrc }}" alt="Omar Al-Sharrah Logo" />
            </div>
        </div>

        <!-- Order & Merchant Information -->
        <div class="row">
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
            <div class="col-6">
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
            <!-- Shipping Address Details -->
            @php
                $displayAddress = $invoice->order->shipping_address ?? $invoice->order->billing_address;
            @endphp

            @if ($displayAddress)
                <div class="table address">
                    <table>
                        <thead>
                            <tr>
                                <th class="table-header" style="width: 100%;">
                                    {{ ucwords(trans('shop::app.customers.account.orders.invoice-pdf.ship-to')) }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    @if (!empty($displayAddress->company_name))
                                        <p><strong>{{ $displayAddress->company_name }}</strong></p>
                                    @endif

                                    <p><strong>{{ $displayAddress->name }}</strong></p>

                                    <p>
                                        @php
                                            $rawAddress = $displayAddress->address;
                                            $addressLines = preg_split('/\r\n|\r|\n/', trim($rawAddress));
                                        @endphp

                                        @if ($displayAddress->country == 'KW' && count($addressLines) > 1)
                                            @php
                                                $cleanLine = function($val) {
                                                    $v = trim($val ?? '');
                                                    return ($v !== '' && $v !== '0' && strtoupper($v) !== 'NA' && strtoupper($v) !== 'N/A') ? $v : null;
                                                };
                                                $block = $cleanLine($addressLines[0] ?? null);
                                                $street = $cleanLine($addressLines[1] ?? null);
                                                $house = $cleanLine($addressLines[2] ?? null);
                                                $floor = $cleanLine($addressLines[3] ?? null);
                                                $flat = $cleanLine($addressLines[4] ?? null);
                                                $avenue = $cleanLine($addressLines[5] ?? null);
                                            @endphp

                                            @if ($block)
                                                <span>{{ app()->getLocale() == 'ar' ? 'القطعة' : 'Block' }}: {{ $block }}</span><br>
                                            @endif
                                            @if ($street)
                                                <span>{{ app()->getLocale() == 'ar' ? 'الشارع' : 'Street' }}: {{ $street }}</span><br>
                                            @endif
                                            @if ($house)
                                                <span>{{ app()->getLocale() == 'ar' ? 'المنزل' : 'House' }}: {{ $house }}{{ $floor ? ' / ' . (app()->getLocale() == 'ar' ? 'الدور' : 'Floor') . ': ' . $floor : '' }}{{ $flat ? ' / ' . (app()->getLocale() == 'ar' ? 'الشقة' : 'Flat') . ': ' . $flat : '' }}</span><br>
                                            @endif
                                            @if ($avenue)
                                                <span>{{ app()->getLocale() == 'ar' ? 'الجادة' : 'Avenue' }}: {{ $avenue }}</span><br>
                                            @endif
                                        @else
                                            {!! nl2br(e($rawAddress)) !!}<br>
                                        @endif
                                    </p>

                                    @if (!empty($displayAddress->postcode) || !empty($displayAddress->city))
                                        <p>{{ trim(($displayAddress->postcode ?? '') . ' ' . ($displayAddress->city ?? '')) }}</p>
                                    @endif

                                    @if (!empty($displayAddress->state))
                                        <p>{{ $displayAddress->state }}</p>
                                    @endif

                                    @if (!empty($displayAddress->country))
                                        <p>{{ core()->country_name($displayAddress->country) }}</p>
                                    @endif

                                    <p><strong>@lang('shop::app.customers.account.orders.invoice-pdf.contact'):</strong> {{ $displayAddress->phone }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

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
                            <th class="text-center table-header" style="width: 13%;">@lang('shop::app.customers.account.orders.invoice-pdf.sku')</th>
                            <th class="align-start table-header" style="width: 31%;">@lang('shop::app.customers.account.orders.invoice-pdf.product-name')</th>
                            <th class="text-center table-header" style="width: 12%;">@lang('shop::app.customers.account.orders.invoice-pdf.price')</th>
                            <th class="text-center table-header" style="width: 8%;">@lang('shop::app.customers.account.orders.invoice-pdf.qty')</th>
                            <th class="text-center table-header" style="width: 12%;">@lang('shop::app.customers.account.orders.invoice-pdf.subtotal')</th>
                            <th class="text-center table-header" style="width: 11%;">@lang('shop::app.customers.account.orders.invoice-pdf.discount')</th>
                            <th class="text-center table-header" style="width: 13%;">@lang('shop::app.customers.account.orders.invoice-pdf.grand-total')</th>
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
            <div class="sale-summary-wrapper">
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
    </div>

    <!-- Automatic Print Trigger on Page Load -->
    <script type="text/javascript">
        window.addEventListener('load', function () {
            var triggerPrint = function () {
                window.print();
            };

            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(triggerPrint).catch(triggerPrint);
            } else {
                triggerPrint();
            }
        });
    </script>
</body>

</html>
