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
        Schema::create('designation_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('designation')->nullable();
            $table->string('rank_number')->nullable();
            $table->integer('priority')->default(0);
            $table->decimal('commission', 5, 2)->default(0);
            $table->decimal('target_from', 15, 2)->default(0);
            $table->decimal('target_to', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designation_ranks');
    }
};