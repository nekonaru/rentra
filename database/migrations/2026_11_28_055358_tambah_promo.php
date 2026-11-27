<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rental')->cascadeOnDelete();
            $table->string('kode', 32);
            $table->string('jenis', 16); // persen / nominal
            $table->decimal('nilai', 12, 2);
            $table->unsignedInteger('minimal_sewa_jam')->default(1);
            $table->unsignedInteger('kuota')->default(100);
            $table->unsignedInteger('terpakai')->default(0);
            $table->dateTime('berlaku_dari')->nullable();
            $table->dateTime('berlaku_sampai')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['rental_id', 'kode']);
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->string('kode_promo')->nullable()->after('kode');
            $table->decimal('diskon_nominal', 12, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['kode_promo', 'diskon_nominal']);
        });
        Schema::dropIfExists('promo');
    }
};
