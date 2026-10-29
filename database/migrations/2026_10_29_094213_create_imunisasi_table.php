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
        Schema::create('imunisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balita')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // petugas
            $table->string('jenis_vaksin');
            $table->date('tanggal');
            $table->string('no_batch')->nullable();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imunisasi');
    }
};
