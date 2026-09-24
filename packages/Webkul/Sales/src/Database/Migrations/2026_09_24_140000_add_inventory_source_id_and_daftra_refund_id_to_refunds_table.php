<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->integer('inventory_source_id')->unsigned()->nullable()->after('order_id');
            $table->unsignedBigInteger('daftra_refund_id')->nullable()->after('inventory_source_id');

            $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['inventory_source_id']);
            $table->dropColumn(['inventory_source_id', 'daftra_refund_id']);
        });
    }
};
