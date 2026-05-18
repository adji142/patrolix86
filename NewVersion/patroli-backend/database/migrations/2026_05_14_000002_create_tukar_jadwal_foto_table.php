<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tukar_jadwal_foto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tukar_jadwal_id');
            $table->string('FileName', 255);
            $table->timestamps();

            $table->foreign('tukar_jadwal_id')->references('id')->on('tukar_jadwal')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tukar_jadwal_foto');
    }
};
