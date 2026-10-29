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
        Schema::create('komposisi_paket_pmt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_pmt')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang_pmt')->restrictOnDelete();
            $table->decimal('jumlah', 8, 2);
            $table->string('unit_type');
            $table->timestamps();
            
            $table->unique(['paket_id', 'barang_id']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komposisi_paket_pmt');
    }
};
