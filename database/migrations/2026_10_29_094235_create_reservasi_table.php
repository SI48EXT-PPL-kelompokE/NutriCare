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
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal_posyandu')->restrictOnDelete();
            $table->foreignId('orang_tua_id')->constrained('orang_tua')->restrictOnDelete();
            $table->foreignId('balita_id')->nullable()->constrained('balita')->restrictOnDelete();
            $table->string('status');
            $table->dateTime('batas_reschedule');
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
