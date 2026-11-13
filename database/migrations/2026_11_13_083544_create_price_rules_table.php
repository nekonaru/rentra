<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('tarif_per_hari', 12, 2)->default(0);
            $table->decimal('tarif_per_jam', 12, 2)->default(0);
            $table->unsignedInteger('minimal_sewa_jam')->default(1);
            $table->unsignedInteger('toleransi_menit')->default(15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
