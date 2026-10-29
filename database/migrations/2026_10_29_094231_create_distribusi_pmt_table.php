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
        Schema::create('distribusi_pmt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balita')->restrictOnDelete();
            $table->foreignId('paket_id')->constrained('paket_pmt')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // petugas
            $table->decimal('jumlah', 8, 2);
            $table->string('status');
            $table->date('handover_date');
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribusi_pmt');
    }
};
