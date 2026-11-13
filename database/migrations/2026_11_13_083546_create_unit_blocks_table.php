<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->dateTime('mulai');
            $table->dateTime('selesai');
            $table->string('alasan')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'mulai', 'selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_blocks');
    }
};
