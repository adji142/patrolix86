<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwalkerja', function (Blueprint $table) {
            $table->id();
            $table->string('KodeKaryawan', 20);
            $table->date('Tanggal');
            $table->tinyInteger('Tgl');
            $table->tinyInteger('Bulan');
            $table->smallInteger('Tahun');
            $table->unsignedBigInteger('shiftid');
            $table->string('RecordOwnerID', 50);
            $table->timestamps();

            $table->unique(['KodeKaryawan', 'Tanggal', 'RecordOwnerID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwalkerja');
    }
};