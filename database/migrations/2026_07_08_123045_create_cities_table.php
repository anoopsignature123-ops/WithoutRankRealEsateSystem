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
        Schema::create('cities', function (Blueprint $table) {
            $table->unsignedInteger('id_city')->primary();
            $table->string('city');
            $table->unsignedInteger('state_id');
            $table->tinyInteger('is_default')->default(1);
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('sort_order')->nullable();
            $table->string('lang', 5)->default('en');
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();

            $table->foreign('state_id')
                ->references('id_state')
                ->on('states')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};