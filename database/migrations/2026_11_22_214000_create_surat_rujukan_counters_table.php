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
        if (!Schema::hasTable('surat_rujukan_counters')) {
            Schema::create('surat_rujukan_counters', function (Blueprint $table) {
                $table->id();
                $table->string('period', 8)->unique(); // Ymd
                $table->unsignedBigInteger('last_sequence')->default(0);
                $table->timestamps();

                $table->engine = 'InnoDB';
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_rujukan_counters');
    }
};
