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
        Schema::create('associates', function (Blueprint $table) {
            $table->id();
            $table->string('associate_id')->unique();
            $table->string('sponsor_id')->nullable();
            $table->string('direction')->nullable();
            $table->string('under_place_id')->nullable();
            $table->foreignId('rank_id')->nullable()->constrained('designation_ranks')->nullOnDelete();
            $table->string('associate_name');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('title')->nullable();
            $table->longText('address')->nullable();
            $table->string('father_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('pancard_number')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('plain_password')->nullable();
            $table->string('aadhar_number')->nullable()->unique();
            $table->string('photo')->nullable();
            $table->string('id_proof_photo')->nullable();
            $table->string('pancard_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associates');
    }
};