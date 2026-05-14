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
        Schema::create('plot_registries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('block_id')->constrained('blocks')->cascadeOnDelete();
            $table->foreignId('plot_detail_id')->constrained('plot_details')->cascadeOnDelete();
            $table->foreignId('customer_booking_id')->constrained('customer_bookings')->cascadeOnDelet();
            $table->string('gata_number')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('register_no')->nullable();
            $table->date('register_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_registries');
    }
};
