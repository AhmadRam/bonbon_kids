<?php

namespace Webkul\Admin\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductReportExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $products = DB::table('products')->get();
        
        $attributeCodes = [
            'name', 'price', 'cost', 'status', 
            'featured', 'new', 'age_group', 'suitable_for',
            'manage_stock', 'allow_rma', 'guest_checkout', 'visible_individually'
        ];
        
        $attributes = DB::table('attributes')->whereIn('code', $attributeCodes)->get()->keyBy('code');
        
        $attributeValues = DB::table('product_attribute_values')
            ->whereIn('attribute_id', $attributes->pluck('id'))
            ->get()
            ->groupBy('product_id');

        // Fetch inventories
        $inventories = DB::table('product_inventories')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Fetch Categories
        $productCategories = DB::table('product_categories')->get()->groupBy('product_id');
        $categoryTranslations = DB::table('category_translations')->where('locale', 'en')->get()->keyBy('category_id');

        // Fetch Options for select/multiselect attributes
        $attributeOptions = DB::table('attribute_option_translations')
            ->where('locale', 'en')
            ->get()
            ->keyBy('attribute_option_id');

        // Fetch product images
        $productImages = DB::table('product_images')->select('product_id')->distinct()->get()->keyBy('product_id');

        $data = [];

        foreach ($products as $product) {
            $productValues = $attributeValues->get($product->id, collect());
            
            $getName = function ($locale) use ($productValues, $attributes) {
                if (!isset($attributes['name'])) return '';
                $val = $productValues->where('attribute_id', $attributes['name']->id)->where('locale', $locale)->first();
                return $val ? $val->text_value : '';
            };
            
            $getPrice = function () use ($productValues, $attributes) {
                if (!isset($attributes['price'])) return 0;
                $val = $productValues->where('attribute_id', $attributes['price']->id)->first();
                return $val ? $val->float_value : 0;
            };

            $getCost = function () use ($productValues, $attributes) {
                if (!isset($attributes['cost'])) return 0;
                $val = $productValues->where('attribute_id', $attributes['cost']->id)->first();
                return $val ? $val->float_value : 0;
            };

            $getBooleanAttr = function ($code) use ($productValues, $attributes) {
                if (!isset($attributes[$code])) return 'No';
                $val = $productValues->where('attribute_id', $attributes[$code]->id)->first();
                return ($val && $val->boolean_value) ? 'Yes' : 'No';
            };

            $getSelectAttr = function ($code) use ($productValues, $attributes, $attributeOptions) {
                if (!isset($attributes[$code])) return '';
                $val = $productValues->where('attribute_id', $attributes[$code]->id)->first();
                if (!$val) return '';
                
                // Select usually stores in integer_value, multiselect in text_value (comma separated ids)
                $optionIds = [];
                if (!empty($val->text_value)) {
                    $optionIds = explode(',', $val->text_value);
                } elseif (!empty($val->integer_value)) {
                    $optionIds = [$val->integer_value];
                }
                
                $labels = [];
                foreach ($optionIds as $optId) {
                    if (isset($attributeOptions[$optId])) {
                        $labels[] = $attributeOptions[$optId]->label;
                    }
                }
                return implode(', ', $labels);
            };

            $getCategories = function () use ($product, $productCategories, $categoryTranslations) {
                if (!isset($productCategories[$product->id])) return '';
                $cats = $productCategories[$product->id];
                $labels = [];
                foreach ($cats as $cat) {
                    if (isset($categoryTranslations[$cat->category_id])) {
                        $labels[] = $categoryTranslations[$cat->category_id]->name;
                    }
                }
                return implode(', ', $labels);
            };
            
            $sku = $product->sku ?? '';
            if (empty($sku)) {
                $skuAttr = DB::table('attributes')->where('code', 'sku')->first();
                if ($skuAttr) {
                    $skuVal = $productValues->where('attribute_id', $skuAttr->id)->first();
                    $sku = $skuVal ? $skuVal->text_value : '';
                }
            }

            $data[] = [
                'Product ID' => $product->id,
                'SKU' => $sku,
                'Product Type' => $product->type,
                'Name (English)' => $getName('en'),
                'Name (Arabic)' => $getName('ar'),
                'Price' => $getPrice(),
                'Cost' => $getCost(),
                'Quantity' => isset($inventories[$product->id]) ? $inventories[$product->id]->total_qty : 0,
                'Status' => $getBooleanAttr('status') === 'Yes' ? 'Active' : 'Inactive',
                'Featured Products' => $getBooleanAttr('featured'),
                'New Arrivals' => $getBooleanAttr('new'),
                'Age Group' => $getSelectAttr('age_group'),
                'Suitable For' => $getSelectAttr('suitable_for'),
                'Categories' => $getCategories(),
                'Manage Stock' => $getBooleanAttr('manage_stock'),
                'Allow RMA' => $getBooleanAttr('allow_rma'),
                'Guest Checkout' => $getBooleanAttr('guest_checkout'),
                'Visible Individually' => $getBooleanAttr('visible_individually'),
                'Has Images' => isset($productImages[$product->id]) ? 'Yes' : 'No',
            ];
        }

        return collect($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Product ID',
            'SKU',
            'Product Type',
            'Name (English)',
            'Name (Arabic)',
            'Price',
            'Cost',
            'Quantity',
            'Status',
            'Featured Products',
            'New Arrivals',
            'Age Group',
            'Suitable For',
            'Categories',
            'Manage Stock',
            'Allow RMA',
            'Guest Checkout',
            'Visible Individually',
            'Has Images',
        ];
    }
}
