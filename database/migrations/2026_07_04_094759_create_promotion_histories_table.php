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
        Schema::create('promotion_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associate_id')->constrained('associates')->cascadeOnDelete();

            $table->foreignId('old_rank_id')->nullable()->constrained('designation_ranks')->nullOnDelete();
            $table->foreignId('new_rank_id')->constrained('designation_ranks')->cascadeOnDelete();

            $table->decimal('self_business', 15, 2)->default(0);
            $table->decimal('team_business', 15, 2)->default(0);
            $table->decimal('total_business', 15, 2)->default(0);

            $table->date('promotion_date')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_histories');
    }
};