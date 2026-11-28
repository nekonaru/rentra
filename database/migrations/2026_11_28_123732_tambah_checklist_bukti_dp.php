<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_dp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->string('jalur');
            $table->decimal('nominal', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->json('checklist_keluar')->nullable()->after('catatan_tolak');
            $table->json('checklist_kembali')->nullable()->after('checklist_keluar');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['checklist_keluar', 'checklist_kembali']);
        });
        Schema::dropIfExists('bukti_dp');
    }
};
