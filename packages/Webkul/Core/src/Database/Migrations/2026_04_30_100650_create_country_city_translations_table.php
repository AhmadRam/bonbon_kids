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
        Schema::create('country_city_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_state_city_id')->unsigned();
            $table->string('locale');
            $table->string('name')->nullable();

            $table->unique(['country_state_city_id', 'locale'], 'country_city_translations_locale_unique');
            $table->foreign('country_state_city_id', 'country_city_translations_city_id_foreign')->references('id')->on('country_state_cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_city_translations');
    }
};
