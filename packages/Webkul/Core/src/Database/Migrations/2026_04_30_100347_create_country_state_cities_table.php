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
        Schema::create('country_state_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->nullable()->unsigned();
            $table->string('country_code')->nullable();
            $table->integer('country_state_id')->nullable()->unsigned();
            $table->string('state_code')->nullable();
            $table->string('code')->nullable();
            $table->string('default_name')->nullable();
            $table->boolean('status')->default(1);

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('country_state_id')->references('id')->on('country_states')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_state_cities');
    }
};
