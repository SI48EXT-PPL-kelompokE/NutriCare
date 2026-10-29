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
        Schema::create('pengukuran_kms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balita')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // petugas
            $table->date('date');
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 1);
            $table->decimal('lingkar_lengan_atas', 4, 1)->nullable();
            $table->decimal('z_score_bbu', 5, 2)->nullable();
            $table->decimal('z_score_tbu', 5, 2)->nullable();
            $table->decimal('z_score_bbtb', 5, 2)->nullable();
            $table->string('status_gizi')->nullable();
            $table->timestamps();
            
            $table->index(['balita_id', 'date']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengukuran_kms');
    }
};
