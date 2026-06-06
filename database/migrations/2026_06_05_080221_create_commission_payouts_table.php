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
        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_generation_id')->nullable()->constrained('commission_generations')->nullOnDelete();
            $table->foreignId('associate_id')->constrained('associates')->cascadeOnDelete();
            $table->foreignId('source_associate_id')->nullable()->constrained('associates')->nullOnDelete();
            $table->foreignId('customer_booking_id')->nullable()->constrained('customer_bookings')->nullOnDelete();
            $table->foreignId('plot_sale_detail_id')->nullable()->constrained('plot_sale_details')->nullOnDelete();
            $table->foreignId('customer_payment_id')->nullable()->constrained('customer_payments')->nullOnDelete();
            $table->string('commission_type')->default('self'); 
            $table->decimal('payment_amount', 15, 2)->default(0);
            $table->foreignId('associate_rank_id')->nullable()->constrained('designation_ranks')->nullOnDelete();
            $table->foreignId('source_rank_id')->nullable()->constrained('designation_ranks')->nullOnDelete();
            $table->decimal('commission_percent', 8, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); 
            $table->date('generated_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->unique(['associate_id', 'customer_payment_id'], 'unique_associate_payment_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_payouts');
    }
};