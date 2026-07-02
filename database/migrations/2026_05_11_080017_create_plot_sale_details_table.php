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
        Schema::create('plot_sale_details', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->nullable();
            $table->foreignId('customer_booking_id')->constrained('customer_bookings')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('block_id')->nullable()->constrained('blocks')->nullOnDelete();
            $table->foreignId('plot_detail_id')->nullable()->constrained('plot_details')->nullOnDelete();
            $table->string('total_development_charge')->nullable();
            $table->string('development_rate')->nullable();
            $table->string('plot_rate')->nullable();
            $table->string('plot_area')->nullable();
            $table->string('plot_cost')->nullable();
            $table->string('plc_amount')->nullable();
            $table->text('remark')->nullable();
            $table->string('other_charges')->nullable();
            $table->string('final_payable')->nullable();
            $table->string('coupon_discount')->nullable();
            $table->string('total_plot_cost')->nullable();
            $table->date('booking_date')->nullable();
            $table->enum('status', ['active', 'cancelled', 'transferred', 'changed'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_sale_details');
    }
};