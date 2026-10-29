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
        Schema::create('detail_distribusi_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_id')->constrained('distribusi_pmt')->cascadeOnDelete();
            $table->foreignId('stok_id')->constrained('stok_gudang')->restrictOnDelete();
            $table->decimal('jumlah_diambil', 10, 2);
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_distribusi_stok');
    }
};
