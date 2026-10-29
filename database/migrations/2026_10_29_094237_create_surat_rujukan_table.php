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
        Schema::create('surat_rujukan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balita')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // nakes
            $table->string('nomor_surat')->unique();
            $table->string('faskes_tujuan');
            $table->string('status_follow_up');
            $table->text('alasan');
            $table->string('qr_verification_code')->unique();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_rujukan');
    }
};
