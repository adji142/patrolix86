<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_izin_foto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengajuan_izin_id');
            $table->string('FileName', 255);

            $table->foreign('pengajuan_izin_id')
                  ->references('id')
                  ->on('pengajuan_izin')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin_foto');
    }
};