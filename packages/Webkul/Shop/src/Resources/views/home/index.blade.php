@php
    $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $channel->home_seo['meta_title'] ?? '' }}"
    />

    <meta
        name="description"
        content="{{ $channel->home_seo['meta_description'] ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $channel->home_seo['meta_keywords'] ?? '' }}"
    />
@endPush

@push('scripts')
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? '' }}
    </x-slot>

    <!-- Loop over the theme customization -->
    @foreach ($customizations as $customization)
        @php ($data = $customization->options) @endphp

        <!-- Static content -->
        @switch ($customization->type)
            @case ($customization::IMAGE_CAROUSEL)
                <!-- Image Carousel -->
                <x-shop::carousel
                    :options="$data"
                    aria-label="{{ trans('shop::app.home.index.image-carousel') }}"
                />

                @break
            @case ($customization::STATIC_CONTENT)
                <!-- push style -->
                @if (! empty($data['css']))
                    @push ('styles')
                        <style>
                            {{ $data['css'] }}
                        </style>
                    @endpush
                @endif

                <!-- render html -->
                @if (! empty($data['html']))
                    {!! $data['html'] !!}
                @endif

                @break
            @case ($customization::CATEGORY_CAROUSEL)
                <!-- Categories carousel -->
                <x-shop::categories.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.categories.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.home.index')"
                    aria-label="{{ trans('shop::app.home.index.categories-carousel') }}"
                />

                @break
            @case ($customization::PRODUCT_CAROUSEL)
                <!-- Product Carousel -->
                <x-shop::products.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.products.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.search.index', $data['filters'] ?? [])"
                    aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
                />

                @break
        @endswitch
    @endforeach
</x-shop::layouts>

@push('styles')
    <style>
        /* Mobile responsive adjustments for custom home sections */
        @media (max-width: 768px) {
            /* General safety rail to prevent page-wide horizontal scroll */
            html, body {
                overflow-x: hidden !important;
                width: 100% !important;
            }

            /* Section Title wrapper responsive spacing and alignment */
            .groups-wrap > div:first-child {
                margin-bottom: 24px !important;
                gap: 8px !important;
            }
            .groups-wrap > div:first-child > div[style*="border-bottom"] {
                max-width: 40px !important;
                border-bottom-width: 2px !important;
            }
            .groups-wrap > div:first-child > span {
                font-size: 18px !important;
            }
            .groups-wrap > div:first-child > h2 {
                font-size: 20px !important;
                text-align: center !important;
            }

            /* "Shop by Categories" - 4 columns layout for circles */
            .groups-circles {
                justify-content: center !important;
                gap: 12px !important;
                margin-bottom: 20px !important;
            }
            .groups-circles .group-card {
                width: 22% !important;
                max-width: 80px !important;
            }
            .groups-circles .group-card a {
                gap: 6px !important;
            }
            .groups-circles .group-card span {
                font-size: 11px !important;
                line-height: 1.2 !important;
            }

            /* "Shop by Categories" - rectangle links layout */
            .groups-rects {
                flex-direction: column !important;
                align-items: center !important;
                gap: 15px !important;
            }
            .groups-rects .group-card-rect {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }
            .groups-rects .g-img-rect {
                height: 120px !important;
            }
            .groups-rects .g-img-rect span {
                font-size: 20px !important;
            }

            /* "Shop by Age" - pills layout */
            .groups-wrap .groups-inner {
                gap: 10px !important;
                justify-content: center !important;
            }
            .age-pill a {
                padding: 10px 20px !important;
                font-size: 15px !important;
                border-radius: 20px !important;
            }

            /* "Who are you shopping for?" - banners layout */
            .group-card-banner {
                width: 48% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }
            .group-card-banner .g-img-banner {
                height: 150px !important;
                border-radius: 16px !important;
            }
            .group-card-banner .g-img-banner span {
                font-size: 22px !important;
            }
        }
    </style>
@endpush

