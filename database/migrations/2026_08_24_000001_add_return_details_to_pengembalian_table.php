<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_kembali')->default(0)->after('tgl_kembali');
            $table->unsignedInteger('terlambat_hari')->default(0)->after('jumlah_kembali');
            $table->text('keterangan')->nullable()->after('denda');
        });
    }

    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->dropColumn(['jumlah_kembali', 'terlambat_hari', 'keterangan']);
        });
    }
};
