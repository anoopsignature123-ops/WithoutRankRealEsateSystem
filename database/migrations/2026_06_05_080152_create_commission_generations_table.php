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
        Schema::create('commission_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associate_id')->constrained('associates')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('self_business', 15, 2)->default(0);
            $table->decimal('team_business', 15, 2)->default(0);
            $table->decimal('self_commission', 15, 2)->default(0);
            $table->decimal('team_commission', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->string('status')->default('generated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_generations');
    }
};