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
        Schema::create('barang_pmt', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori');
            $table->string('unit_type');
            $table->integer('min_batas_stok');
            $table->decimal('energi_kcal', 8, 2);
            $table->decimal('protein', 8, 2);
            $table->decimal('lemak', 8, 2);
            $table->decimal('karbohidrat', 8, 2);
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_pmt');
    }
};
