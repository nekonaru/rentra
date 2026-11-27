<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental', function (Blueprint $table) {
            $table->time('jam_buka')->nullable()->after('durasi_tahan_menit');
            $table->time('jam_tutup')->nullable()->after('jam_buka');
            $table->decimal('denda_per_jam', 12, 2)->default(0)->after('persen_dp');
            $table->decimal('denda_maks', 12, 2)->default(0)->after('denda_per_jam');
        });

        Schema::table('aturan_tarif', function (Blueprint $table) {
            $table->decimal('tarif_weekend', 12, 2)->nullable()->after('tarif_per_jam');
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rental')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('aktivitas', 120);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['rental_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::table('aturan_tarif', function (Blueprint $table) {
            $table->dropColumn('tarif_weekend');
        });
        Schema::table('rental', function (Blueprint $table) {
            $table->dropColumn(['jam_buka', 'jam_tutup', 'denda_per_jam', 'denda_maks']);
        });
    }
};
