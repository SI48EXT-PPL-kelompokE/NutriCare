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
        Schema::table('jadwal_posyandu', function (Blueprint $table) {
            $table->time('jam_selesai')->nullable()->after('hour');
            $table->string('status', 20)->default('aktif')->after('kuota_jadwal');
            $table->index(['user_id', 'date', 'status'], 'idx_jadwal_nakes_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_posyandu', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_nakes_date_status');
            $table->dropColumn(['jam_selesai', 'status']);
        });
    }
};

