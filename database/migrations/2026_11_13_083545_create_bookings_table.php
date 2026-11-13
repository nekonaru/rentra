<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->string('nama_penyewa');
            $table->string('wa', 32);
            $table->dateTime('mulai');
            $table->dateTime('selesai');
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('dp_nominal', 12, 2)->default(0);
            $table->string('status')->default('ditahan')->index();
            $table->dateTime('hold_expires_at')->nullable();
            $table->string('bukti_path')->nullable();
            $table->string('catatan_tolak')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'mulai', 'selesai']);
            $table->index(['status', 'hold_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
