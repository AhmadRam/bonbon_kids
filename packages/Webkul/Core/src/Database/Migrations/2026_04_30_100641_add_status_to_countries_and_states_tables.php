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
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('status')->default(1)->after('name');
        });

        Schema::table('country_states', function (Blueprint $table) {
            $table->boolean('status')->default(1)->after('default_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('country_states', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
