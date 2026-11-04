<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $attribute = DB::table('attributes')->where('code', 'manage_stock')->first();

        if (! $attribute) {
            return;
        }

        // Set manage_stock = 1 for all existing product attribute values
        DB::table('product_attribute_values')
            ->where('attribute_id', $attribute->id)
            ->update(['boolean_value' => 1]);

        // Insert manage_stock = 1 for any products that don't have this attribute value yet
        $defaultChannel = DB::table('channels')->where('code', 'default')->first()?->code ?? 'default';

        $missingProductIds = DB::table('products')
            ->leftJoin('product_attribute_values', function ($join) use ($attribute) {
                $join->on('products.id', '=', 'product_attribute_values.product_id')
                    ->where('product_attribute_values.attribute_id', '=', $attribute->id);
            })
            ->whereNull('product_attribute_values.id')
            ->pluck('products.id');

        $toInsert = [];

        foreach ($missingProductIds as $productId) {
            $toInsert[] = [
                'product_id' => $productId,
                'attribute_id' => $attribute->id,
                'channel' => $defaultChannel,
                'locale' => null,
                'boolean_value' => 1,
            ];
        }

        if (! empty($toInsert)) {
            DB::table('product_attribute_values')->insert($toInsert);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
