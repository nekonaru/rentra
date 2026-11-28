<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('deskripsi');
            $table->string('peta_url', 500)->nullable()->after('alamat');
        });

        Schema::table('unit', function (Blueprint $table) {
            $table->string('kode_internal', 80)->nullable()->after('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('unit', function (Blueprint $table) {
            $table->dropColumn('kode_internal');
        });
        Schema::table('rental', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'peta_url']);
        });
    }
};
