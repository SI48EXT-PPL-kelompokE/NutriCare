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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete(); // admin penanggap
            $table->foreignId('orang_tua_id')->nullable()->constrained('orang_tua')->restrictOnDelete();
            $table->string('kategori');
            $table->string('judul');
            $table->text('isi');
            $table->boolean('is_anonymous')->default(false);
            $table->string('foto_bukti_path')->nullable();
            $table->string('status'); // baru, diproses, selesai
            $table->text('catatan')->nullable();
            $table->string('nomor_tiket')->unique();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
