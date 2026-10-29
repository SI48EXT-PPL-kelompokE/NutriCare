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
        Schema::create('paket_pmt', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket_pmt');
            $table->text('deskripsi')->nullable();
            $table->integer('min_age_target'); // bulan
            $table->integer('max_age_target'); // bulan
            $table->string('unit_type');
            $table->boolean('status')->default(true);
            $table->decimal('total_energi_kcal', 8, 2)->default(0);
            $table->decimal('total_protein', 8, 2)->default(0);
            $table->decimal('total_lemak', 8, 2)->default(0);
            $table->decimal('total_karbohidrat', 8, 2)->default(0);
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_pmt');
    }
};
