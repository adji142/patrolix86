<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('RecordOwnerID');
            $table->string('KodeKaryawan', 50);
            $table->date('TglCutiAwal');
            $table->date('TglCutiAkhir');
            $table->date('TglPencatatan');
            $table->text('KeteranganCuti');
            $table->string('KategoriCuti', 50);
            $table->tinyInteger('Approval')->default(0); // 0=Pending, 1=Disetujui, 2=Ditolak
            $table->string('ApprovedBy', 100)->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->text('CatatanApproval')->nullable();
            $table->dateTime('CreatedOn');
            $table->dateTime('UpdatedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};