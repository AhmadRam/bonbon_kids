<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="{{ app()->getLocale() }}" dir="{{ core()->getCurrentLocale()->direction }}">

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
        }

        .merchant-details {
            margin-bottom: 30px;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
        }

        .table thead th {
            font-weight: 700;
            color: #000;
            font-size: 12px;
            border: solid 1px #d3d3d3;
            background: #F8F9FA;
            padding: 15px 10px;
        }

        .table tbody td {
            color: #000;
            font-size: 12px;
            border: solid 1px #d3d3d3;
            padding: 15px 10px;
        }

        .print-header {
            background: #f8f9fa;
            border-bottom: 3px solid #007cba;
            padding: 15px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .content-wrapper {
            margin-top: 80px;
        }

        @media print {
            .print-header {
                display: none !important;
            }

            .content-wrapper {
                margin-top: 0 !important;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            margin: 0 5px;
        }

        .btn-primary {
            background: #007cba;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .kbd {
            background: #eee;
            padding: 3px 6px;
            border-radius: 3px;
            font-family: monospace;
        }

        .signature-section {
            margin-top: 20px;
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

    <!-- Print Instructions Header -->
    <div class="print-header">
        <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
            <div style="font-size: 16px; font-weight: bold; color: #333;">
                🖨️ {{ $isRTL ? 'لطباعة هذا المستند:' : 'To print this document:' }}
            </div>
            <div style="display: flex; gap: 15px; align-items: center;">
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ {{ $isRTL ? 'طباعة' : 'Print' }}
                </button>
                <span style="color: #666; font-size: 14px;">
                    {{ $isRTL ? 'أو اضغط' : 'Or press' }} <span class="kbd">Ctrl+P</span>
                </span>
                <button onclick="window.close()" class="btn btn-secondary" style="padding: 8px 16px; font-size: 12px;">
                    ✕ {{ $isRTL ? 'إغلاق' : 'Close' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12 header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
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
                        <span style="font-size: 24px; font-weight: bold;">
                            {{ $isRTL ? 'تقرير نقل المخزون الجماعي' : 'MASS INVENTORY TRANSFER REPORT' }}
                        </span><br><br>
                        <span style="color: #000;font-size:18px">
                            {{ core()->getConfigData('sales.shipping.origin.store_name') ?? 'Dar Al-Wafaa Trading Company' }}
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

            <!-- Transfer Details -->
            <div class="merchant-details">
                <div class="merchant-details-title"
                    style="font-weight: 600; font-size: 16px; color: #000; margin-bottom: 15px;">
                    {{ $isRTL ? 'تفاصيل عمليات النقل:' : 'Transfer Details:' }}
                </div>

                <div style="font-size: 12px; margin-bottom: 20px; color: #666;">
                    <div>{{ $isRTL ? 'تاريخ التقرير:' : 'Report Date:' }}
                        {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</div>
                    <div>{{ $isRTL ? 'عدد العمليات:' : 'Number of Transfers:' }} {{ count($transfers) }}</div>
                </div>
            </div>

            <!-- Transfers Table -->
            <div class="order-summary" style="margin-top: 20px;">
                <table class="table">
                    <thead>
                        <tr style="background-color: #f2f2f2">
                            <th>{{ $isRTL ? 'الصنف' : 'Product' }}</th>
                            <th>{{ $isRTL ? 'الرمز' : 'SKU' }}</th>
                            <th>{{ $isRTL ? 'سيريال نمبر' : 'Serial Number' }}</th>
                            <th>{{ $isRTL ? 'من' : 'From' }}</th>
                            <th>{{ $isRTL ? 'إلى' : 'To' }}</th>
                            <th>{{ $isRTL ? 'الكمية' : 'Quantity' }}</th>
                            <th>{{ $isRTL ? 'التاريخ' : 'Date' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalQuantity = 0; @endphp
                        @foreach ($transfers as $transfer)
                            @php $totalQuantity += $transfer->quantity; @endphp
                            <tr>
                                <td>{{ $transfer->product_name }}</td>
                                <td>{{ $transfer->product_sku }}</td>
                                <td>{{ $transfer->product_number ?? '-' }}</td>
                                <td>{{ $transfer->from_name === 'Default' ? 'Hawalli' : $transfer->from_name }}</td>
                                <td>{{ $transfer->to_name === 'Default' ? 'Hawalli' : $transfer->to_name }}</td>
                                <td>{{ $transfer->quantity }}</td>
                                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d-m-Y H:i') }}</td>
                            </tr>
                        @endforeach
                        <!-- Total Row -->
                        <tr style="background-color: #f8f9fa; font-weight: bold;">
                            <td colspan="5">{{ $isRTL ? 'إجمالي الكمية:' : 'Total Quantity:' }}</td>
                            <td style="color: #007cba;">{{ $totalQuantity }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="row" style="text-align: {{ $isRTL ? 'right' : 'left' }};">
                    <div style="width: 100%; margin: 0 auto;">
                        <div class="merchant-details-title" style="margin-bottom: 20px; font-size: 14px;">
                            {{ $isRTL ? 'الاسم والتوقيع:' : 'Name and Signature:' }}
                        </div>
                        <div class="signature-box"
                            style="height: 120px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div style="margin-bottom: 40px;">
                                <span>{{ $isRTL ? 'الاسم:' : 'Name:' }}</span>
                                <div
                                    style="border-bottom: solid 1px #000; width: 300px; display: inline-block; margin: 0 10px;">
                                </div>
                            </div>
                            <div>
                                <span>{{ $isRTL ? 'التوقيع:' : 'Signature:' }}</span>
                                <div
                                    style="border-bottom: solid 1px #000; width: 300px; display: inline-block; margin: 0 10px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                style="margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 15px;">
                {{ $isRTL
                    ? 'تم إنشاء هذا المستند آلياً في ' . \Carbon\Carbon::now()->format('d-m-Y H:i:s')
                    : 'This document was generated automatically at ' . \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
            </div>
        </div>
    </div>
</body>

</html>
