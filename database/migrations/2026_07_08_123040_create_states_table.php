<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->unsignedInteger('id_state')->primary();
            $table->string('state');
            $table->unsignedInteger('country_id')->default(101);
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('sort_order')->nullable();
            $table->string('lang', 5)->default('en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};