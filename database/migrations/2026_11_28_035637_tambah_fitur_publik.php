<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit')->cascadeOnDelete();
            $table->string('jalur');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['unit_id', 'urutan']);
        });

        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->unique()->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('unit')->cascadeOnDelete();
            $table->foreignId('rental_id')->constrained('rental')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('komentar');
            $table->string('status')->default('menunggu');
            $table->timestamps();

            $table->index(['unit_id', 'status']);
        });

        Schema::table('unit', function (Blueprint $table) {
            $table->text('spesifikasi')->nullable()->after('kategori');
            $table->string('merek', 80)->nullable()->after('spesifikasi');
            $table->unsignedSmallInteger('tahun')->nullable()->after('merek');
        });

        Schema::table('rental', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('nama');
            $table->string('warna_aksen', 7)->nullable()->after('logo_path');
            $table->text('deskripsi')->nullable()->after('warna_aksen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
        Schema::dropIfExists('foto_unit');
        Schema::table('unit', function (Blueprint $table) {
            $table->dropColumn(['spesifikasi', 'merek', 'tahun']);
        });
        Schema::table('rental', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'warna_aksen', 'deskripsi']);
        });
    }
};
