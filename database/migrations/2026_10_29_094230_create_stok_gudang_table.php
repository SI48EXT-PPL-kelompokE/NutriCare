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
        Schema::create('stok_gudang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang_pmt')->restrictOnDelete();
            $table->string('no_batch');
            $table->date('date_in');
            $table->date('exp_date');
            $table->decimal('stock_qty', 10, 2);
            $table->timestamps();
            
            $table->index('exp_date');
            $table->unique(['barang_id', 'no_batch']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_gudang');
    }
};
