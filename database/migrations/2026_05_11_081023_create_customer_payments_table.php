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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_booking_id')->constrained('customer_bookings')->cascadeOnDelete();
            $table->foreignId('plot_sale_detail_id')->nullable()->constrained('plot_sale_details')->nullOnDelete();
            $table->string('receipt_number')->nullable();
            $table->string('manual_receipt_number')->nullable();
            $table->enum('plan_type', ['full_payment', 'emi_plan'])->nullable();
            $table->string('booking_amount')->nullable();
            $table->string('due_amount')->nullable();
            $table->string('net_payable_amount')->nullable();
            $table->integer('emi_months')->nullable();
            $table->string('after_booking_payable_amount')->nullable();
            $table->text('remark')->nullable();
            $table->enum('payment_mode', ['cash', 'cheque', 'dd', 'neft_rtgs', 'card'])->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('dd_number')->nullable();
            $table->string('transaction_number')->nullable()->comment('NEFT / RTGS / Card Transaction Reference Number');
            $table->enum('payment_status', ['booked', 'hold', 'emi'])->default('hold');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
