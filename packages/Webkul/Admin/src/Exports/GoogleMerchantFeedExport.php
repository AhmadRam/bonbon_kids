<?php

namespace Webkul\Admin\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Facades\ProductImage;

class GoogleMerchantFeedExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $locale;

    protected $currencyCode;

    public function __construct($locale = 'ar', $currencyCode = 'KWD')
    {
        $this->locale = $locale;
        $this->currencyCode = $currencyCode;
    }

    public function query()
    {
        return Product::query()->whereHas('attribute_family');
    }

    public function headings(): array
    {
        return [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'availability',
            'price',
            'brand',
            'condition',
            'age_group',
            'gtin',
            'mpn'
        ];
    }

    public function map($product): array
    {
        app()->setLocale($this->locale);

        // We only want active & visible products
        if (!$product->status || !$product->visible_individually) {
            return [];
        }

        // Get product type instance to get price
        $productTypeInstance = $product->getTypeInstance();
        
        $price = $productTypeInstance->getMinimalPrice();
        if (!$price && $productTypeInstance->getMaximumPrice()) {
            $price = $productTypeInstance->getMaximumPrice();
        }

        $formattedPrice = number_format((float)$price, 3, '.', '') . ' ' . $this->currencyCode;

        // Availability logic
        $quantity = $product->totalQuantity();
        $availability = ($quantity > 0) ? 'in_stock' : 'out_of_stock';

        // Get brand and age_group
        $brand = $this->getAttributeOptionLabel($product->brand);
        $ageGroup = $this->getAttributeOptionLabel($product->age_group);
        
        $gtin = $product->gtin ?? '';
        $mpn = $product->mpn ?? '';

        $urlKey = $product->url_key;
        if (!$urlKey) {
            return []; // Skip if no valid URL key
        }

        return [
            $product->sku ?? $product->id,
            $product->name,
            html_entity_decode(strip_tags($product->short_description ?: $product->description), ENT_QUOTES, 'UTF-8'),
            config('app.url') . '/' . $urlKey,
            ProductImage::getProductBaseImage($product)['original_image_url'] ?? '',
            $availability,
            $formattedPrice,
            $brand,
            'new',
            $ageGroup,
            $gtin,
            $mpn
        ];
    }
    
    protected function getAttributeOptionLabel($optionId)
    {
        if (!$optionId) {
            return '';
        }
        
        $option = app(AttributeOptionRepository::class)->find($optionId);
        if ($option) {
            $translation = $option->translate($this->locale);
            return $translation ? $translation->label : $option->admin_name;
        }
        
        return '';
    }
}
