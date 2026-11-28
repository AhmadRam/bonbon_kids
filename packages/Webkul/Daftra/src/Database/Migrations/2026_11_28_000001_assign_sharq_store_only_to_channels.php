<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restrict storefront channel inventory sources to Sharq Store (store_id = 2) only.
     * Existing inventory_sources and product_inventories rows for other warehouses are preserved
     * for admin inventory reporting.
     */
    public function up(): void
    {
        if (
            ! Schema::hasTable('inventory_sources')
            || ! Schema::hasTable('channel_inventory_sources')
            || ! Schema::hasTable('channels')
        ) {
            return;
        }

        $sharqStoreId = (int) config('daftra.store_id', 2);

        if (! DB::table('inventory_sources')->where('id', $sharqStoreId)->exists()) {
            return;
        }

        DB::table('inventory_sources')
            ->where('id', $sharqStoreId)
            ->update(['status' => 1]);

        $channelIds = DB::table('channels')->pluck('id');

        foreach ($channelIds as $channelId) {
            DB::table('channel_inventory_sources')
                ->where('channel_id', $channelId)
                ->where('inventory_source_id', '!=', $sharqStoreId)
                ->delete();

            DB::table('channel_inventory_sources')->insertOrIgnore([
                'channel_id' => $channelId,
                'inventory_source_id' => $sharqStoreId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left non-destructive; re-attach additional sources via Admin if needed.
    }
};
