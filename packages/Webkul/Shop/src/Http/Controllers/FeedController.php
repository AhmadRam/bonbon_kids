<?php

namespace Webkul\Shop\Http\Controllers;

use Webkul\Product\Models\Product;
use Webkul\Product\Facades\ProductImage;
use Webkul\Attribute\Repositories\AttributeOptionRepository;

class FeedController extends Controller
{
    /**
     * Generate Google Merchant / Meta XML Feed
     *
     * @return \Illuminate\Http\Response
     */
    public function googleMerchantFeed()
    {
        $locale = request()->get('locale', 'ar');
        app()->setLocale($locale);
        $currencyCode = 'KWD';
        
        $products = Product::query()->whereHas('attribute_family')->get();
        
        $feedItems = [];
        $attributeOptionRepository = app(AttributeOptionRepository::class);

        foreach ($products as $product) {
            if (!$product->status || !$product->visible_individually) {
                continue;
            }

            $urlKey = $product->url_key;
            if (!$urlKey) {
                continue;
            }
            
            $productTypeInstance = $product->getTypeInstance();
            
            $price = $productTypeInstance->getMinimalPrice();
            if (!$price && $productTypeInstance->getMaximumPrice()) {
                $price = $productTypeInstance->getMaximumPrice();
            }

            $formattedPrice = number_format((float)$price, 3, '.', '') . ' ' . $currencyCode;
            $quantity = $product->totalQuantity();
            $availability = ($quantity > 0) ? 'in_stock' : 'out_of_stock';
            
            $getLabel = function ($optionId) use ($locale, $attributeOptionRepository) {
                if (!$optionId) return '';
                $option = $attributeOptionRepository->find($optionId);
                if ($option) {
                    $translation = $option->translate($locale);
                    return $translation ? $translation->label : $option->admin_name;
                }
                return '';
            };

            $brand = $getLabel($product->brand);
            $ageGroup = $getLabel($product->age_group);
            $gtin = $product->gtin ?? '';
            $mpn = $product->mpn ?? '';
            
            $feedItems[] = [
                'id' => $product->sku ?? $product->id,
                'title' => $product->name,
                'description' => html_entity_decode(strip_tags($product->short_description ?: $product->description), ENT_QUOTES, 'UTF-8'),
                'link' => config('app.url') . '/' . $urlKey,
                'image_link' => ProductImage::getProductBaseImage($product)['original_image_url'] ?? '',
                'availability' => $availability,
                'price' => $formattedPrice,
                'brand' => $brand,
                'condition' => 'new',
                'age_group' => $ageGroup,
                'gtin' => $gtin,
                'mpn' => $mpn
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '  <title>BonBon Kids Store KW</title>' . "\n";
        $xml .= '  <link>' . config('app.url') . '</link>' . "\n";
        $xml .= '  <description>Product feed for BonBon Kids Store</description>' . "\n";
        
        foreach ($feedItems as $item) {
            $xml .= "  <item>\n";
            $xml .= "    <g:id>" . htmlspecialchars($item['id']) . "</g:id>\n";
            $xml .= "    <g:title>" . htmlspecialchars($item['title']) . "</g:title>\n";
            $xml .= "    <g:description>" . htmlspecialchars($item['description']) . "</g:description>\n";
            $xml .= "    <g:link>" . htmlspecialchars($item['link']) . "</g:link>\n";
            if ($item['image_link']) {
                $xml .= "    <g:image_link>" . htmlspecialchars($item['image_link']) . "</g:image_link>\n";
            }
            $xml .= "    <g:availability>" . htmlspecialchars($item['availability']) . "</g:availability>\n";
            $xml .= "    <g:price>" . htmlspecialchars($item['price']) . "</g:price>\n";
            if ($item['brand']) {
                $xml .= "    <g:brand>" . htmlspecialchars($item['brand']) . "</g:brand>\n";
            }
            $xml .= "    <g:condition>new</g:condition>\n";
            if ($item['age_group']) {
                $xml .= "    <g:age_group>" . htmlspecialchars($item['age_group']) . "</g:age_group>\n";
            }
            if ($item['gtin']) {
                $xml .= "    <g:gtin>" . htmlspecialchars($item['gtin']) . "</g:gtin>\n";
            }
            if ($item['mpn']) {
                $xml .= "    <g:mpn>" . htmlspecialchars($item['mpn']) . "</g:mpn>\n";
            }
            $xml .= "  </item>\n";
        }

        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';

        return response($xml)->header('Content-Type', 'application/xml');
    }
}
