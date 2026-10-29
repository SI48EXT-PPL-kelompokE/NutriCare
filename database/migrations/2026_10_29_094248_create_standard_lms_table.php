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
        Schema::create('standard_lms', function (Blueprint $table) {
            $table->id();
            $table->string('indikator', 10);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->decimal('acuan_nilai', 5, 1); // umur bulan untuk BB_U/TB_U atau panjang/tinggi cm untuk BB_PB/BB_TB
            $table->decimal('l', 8, 5);
            $table->decimal('m', 8, 5);
            $table->decimal('s', 8, 5);
            $table->decimal('sd_min3', 6, 1);
            $table->decimal('sd_min2', 6, 1);
            $table->decimal('sd_min1', 6, 1);
            $table->decimal('sd_0', 6, 1);
            $table->decimal('sd_plus1', 6, 1);
            $table->decimal('sd_plus2', 6, 1);
            $table->decimal('sd_plus3', 6, 1);
            $table->timestamps();
            
            $table->unique(['indikator', 'jenis_kelamin', 'acuan_nilai']);
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_lms');
    }
};
