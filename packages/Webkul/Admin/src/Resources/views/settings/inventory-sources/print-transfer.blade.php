{{ app()->setLocale('ar') }}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">

<head>
    <!-- meta tags -->
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- lang supports inclusion -->
    <style type="text/css">
        @font-face {
            font-family: 'Hind';
            src: url({{ asset('vendor/webkul/ui/assets/fonts/Hind/Hind-Regular.ttf') }}) format('truetype');
        }

        @font-face {
            font-family: 'Noto Sans';
            src: url({{ asset('vendor/webkul/ui/assets/fonts/Noto/NotoSans-Regular.ttf') }}) format('truetype');
        }
    </style>
    <script>
        window.print();
    </script>
    @php
        /* main font will be set on locale based */
        $mainFontFamily = app()->getLocale() === 'ar' ? 'DejaVu Sans' : 'Noto Sans';
        $isRTL = app()->getLocale() === 'ar';
    @endphp

    <!-- main css -->
    <style type="text/css">
        * {
            font-family: '{{ $mainFontFamily }}';
        }

        body,
        th,
        td,
        h5 {
            font-size: 12px;
            color: #000;
        }

        .container {
            padding: 20px;
            display: block;
        }

        .transfer-summary {
            margin-bottom: 20px;
        }

        .transfer-summary td {
            padding: 5px 10px;
            border: solid 1px #d3d3d3;
        }

        .transfer-summary th {
            padding: 5px 10px;
            color: #0041FF;
            border: solid 1px #d3d3d3;
            background: #F4F4F4;
        }

        .transfer-summary tr.bold {
            font-weight: 700;
        }

        .label {
            color: #000;
            font-weight: bold;
        }

        .logo {
            height: 70px;
            width: 70px;
        }

        .merchant-details {
            margin-bottom: 5px;
        }

        .merchant-details-title {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .col-6 {
            width: 42%;
            display: inline-block;
            vertical-align: top;
            margin: 0px 5px;
        }

        .table-header {
            color: #0041FF;
        }

        .align-left {
            text-align: {{ $isRTL ? 'right' : 'left' }};
        }

        .text-right {
            text-align: {{ $isRTL ? 'right' : 'left' }};
        }

        .text-left {
            text-align: {{ $isRTL ? 'left' : 'right' }};
        }

        .transfer-text {
            font-size: 32px;
            color: #3c41ff;
            font-weight: bold;
            position: absolute;
            width: 100%;
            left: 0;
            text-align: center;
            top: -6px;
        }

        .without_logo {
            height: 35px;
            width: 35px;
        }

        .header {
            padding: 0px 2px;
            width: 100%;
            position: relative;
            border-bottom: solid 1px #d3d3d3;
            padding-bottom: 20px;
        }

        .signature-section {
            margin-top: 50px;
            border-top: solid 1px #d3d3d3;
            padding-top: 20px;
        }

        .signature-box {
            border: solid 1px #d3d3d3;
            padding: 20px;
            margin: 10px 0;
            height: 80px;
        }
    </style>
</head>

<body style="background-image: none; background-color: #fff;">
    <div class="container">
        <div class="row">
            <div class="col-12 header" style="display: flex; justify-content: space-between; align-items: center;">
                <!-- Left Image (Dynamic Logo) -->
                <div class="image" style="margin-left: 20px;">
                    @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                        <img style="max-width: 140px; max-height: 140px;" src="{{ Storage::url($logo) }}" alt="Logo" />
                    @else
                        <img style="max-width: 140px; max-height: 140px;" src="{{ bagisto_asset('images/logo.svg') }}" alt="Logo" />
                    @endif
                </div>

                <!-- Transfer Text in the Middle -->
                <div class="transfer-text" style="text-align: center;">
                    <span>{{ $isRTL
                        ? 'سند تحويل بضاعة'
                        : 'INVENTORY TRANSFER' }}</span><br><br>
                    <span style="color: #000;font-size:18px">
                        {{ 'شركة عمر خالد الشراح' }}
                    </span>
                </div>

                <!-- Right Image -->
                <div class="image" style="margin-right: 20px;">
                    @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                        <img style="max-height: 140px;" src="{{ Storage::url($logo) }}" alt="Logo" />
                    @endif
                </div>
            </div>
        </div>

        <div class="row" style="padding: 5px">
            <div class="col-12">
                <div class="col-6">
                    <div class="merchant-details">
                        <div class="row">
                            <span class="label">{{ $isRTL ? 'رقم النقل:' : 'Transfer ID:' }} </span>
                            <span class="value">#{{ $transfer->id }}</span>
                        </div>

                        <div class="row">
                            <span class="label">{{ $isRTL ? 'التاريخ:' : 'Date:' }} </span>
                            <span
                                class="value">{{ \Carbon\Carbon::parse($transfer->created_at)->format('d-m-Y') }}</span>
                        </div>

                        <!-- Removed store_name / الشركة -->

                        <div>{{ core()->getConfigData('sales.shipping.origin.address1') ?? '' }}</div>

                        <div>
                            <span>{{ core()->getConfigData('sales.shipping.origin.zipcode') ?? '' }}</span>
                            <span>{{ core()->getConfigData('sales.shipping.origin.city') ?? '' }}</span>
                        </div>

                        <div>{{ core()->getConfigData('sales.shipping.origin.state') ?? '' }}</div>

                        <div>{{ core()->getConfigData('sales.shipping.origin.country') ?? '' }}</div>
                    </div>
                    <div class="merchant-details">
                        @if (core()->getConfigData('sales.shipping.origin.contact'))
                            <div><span class="merchant-details-title">{{ $isRTL ? 'رقم الاتصال:' : 'Contact Number:' }}
                                </span>
                                {{ core()->getConfigData('sales.shipping.origin.contact') }}</div>
                        @endif

                        @if (core()->getConfigData('sales.shipping.origin.vat_number'))
                            <div><span class="merchant-details-title">{{ $isRTL ? 'الرقم الضريبي:' : 'VAT Number:' }}
                                </span>
                                {{ core()->getConfigData('sales.shipping.origin.vat_number') }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-6" style="padding-{{ $isRTL ? 'right' : 'left' }}: 80px">
                    <div style="padding-top: 20px">
                        <span class="merchant-details-title">{{ $isRTL ? 'تفاصيل النقل' : 'Transfer Details' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 20px 0;">
            <table class="transfer-summary" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                            {{ $isRTL ? 'الرمز' : 'SKU' }}
                        </th>
                        <th style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                            {{ $isRTL ? 'الصنف' : 'Product' }}
                        </th>
                        <th style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                            {{ $isRTL ? 'سيريال نمبر' : 'Serial Number' }}
                        </th>
                        <th style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                            {{ $isRTL ? 'من' : 'From' }}
                        </th>
                        <th style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                            {{ $isRTL ? 'إلى' : 'To' }}
                        </th>
                        <th style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                            {{ $isRTL ? 'الكمية' : 'Quantity' }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: {{ $isRTL ? 'right' : 'left' }}; padding: 15px 10px;">
                            {{ $transfer->product_sku }}
                        </td>
                        <td style="text-align: {{ $isRTL ? 'right' : 'left' }}; padding: 15px 10px;">
                            {{ $transfer->product_name }}
                        </td>
                        <td style="text-align: {{ $isRTL ? 'right' : 'left' }}; padding: 15px 10px;">
                            {{ $transfer->product_number ?? '-' }}
                        </td>
                        <td style="text-align: {{ $isRTL ? 'right' : 'left' }}; padding: 15px 10px;">
                            {{ $transfer->from_name === 'Default' || $transfer->from_name === 'Hawalli' ? ($isRTL ? 'المخزن الرئيسي' : 'Main Warehouse') : $transfer->from_name }}
                        </td>
                        <td style="text-align: {{ $isRTL ? 'right' : 'left' }}; padding: 15px 10px;">
                            {{ $transfer->to_name === 'Default' || $transfer->to_name === 'Hawalli' ? ($isRTL ? 'المخزن الرئيسي' : 'Main Warehouse') : $transfer->to_name }}
                        </td>
                        <td style="text-align: {{ $isRTL ? 'right' : 'left' }}; padding: 15px 10px;">
                            {{ $transfer->quantity }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="row" style="text-align: {{ $isRTL ? 'right' : 'left' }}; display: flex; justify-content: space-between;">
                <!-- Sending Section -->
                <div style="width: 48%;">
                    <div class="merchant-details-title" style="margin-bottom: 20px; font-size: 14px;">
                        {{ $isRTL ? 'قسم الإرسال:' : 'Sending Section:' }}
                    </div>
                    <div class="signature-box" style="height: auto; padding: 15px;">
                        <div style="margin-bottom: 20px;">
                            <span style="display: inline-block; width: 60px;">{{ $isRTL ? 'الاسم:' : 'Name:' }}</span>
                            <div style="border-bottom: solid 1px #000; width: calc(100% - 70px); display: inline-block;"></div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <span style="display: inline-block; width: 60px;">{{ $isRTL ? 'التوقيع:' : 'Signature:' }}</span>
                            <div style="border-bottom: solid 1px #000; width: calc(100% - 70px); display: inline-block;"></div>
                        </div>
                        <div>
                            <span style="display: inline-block; width: 60px;">{{ $isRTL ? 'التاريخ:' : 'Date:' }}</span>
                            <div style="border-bottom: solid 1px #000; width: calc(100% - 70px); display: inline-block;"></div>
                        </div>
                    </div>
                </div>

                <!-- Receiving Section -->
                <div style="width: 48%;">
                    <div class="merchant-details-title" style="margin-bottom: 20px; font-size: 14px;">
                        {{ $isRTL ? 'قسم الاستلام:' : 'Receiving Section:' }}
                    </div>
                    <div class="signature-box" style="height: auto; padding: 15px;">
                        <div style="margin-bottom: 20px;">
                            <span style="display: inline-block; width: 60px;">{{ $isRTL ? 'الاسم:' : 'Name:' }}</span>
                            <div style="border-bottom: solid 1px #000; width: calc(100% - 70px); display: inline-block;"></div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <span style="display: inline-block; width: 60px;">{{ $isRTL ? 'التوقيع:' : 'Signature:' }}</span>
                            <div style="border-bottom: solid 1px #000; width: calc(100% - 70px); display: inline-block;"></div>
                        </div>
                        <div>
                            <span style="display: inline-block; width: 60px;">{{ $isRTL ? 'التاريخ:' : 'Date:' }}</span>
                            <div style="border-bottom: solid 1px #000; width: calc(100% - 70px); display: inline-block;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
            {{ $isRTL
                ? 'تم إنشاء هذا المستند آلياً في ' . \Carbon\Carbon::now()->format('d-m-Y H:i:s')
                : 'This document was generated automatically at ' . \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
        </div>
    </div>
</body>

</html>
