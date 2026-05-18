<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tukar_jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('RecordOwnerID', 50);
            $table->string('KodeKaryawan', 50);
            $table->date('TanggalTukar');
            $table->date('TglPencatatan');
            $table->text('Keterangan')->nullable();
            $table->tinyInteger('Approval')->default(0)->comment('0=Pending, 1=Approved, 2=Rejected');
            $table->string('ApprovedBy', 100)->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('idJadwalAwal');
            $table->unsignedBigInteger('idJadwalBaru')->nullable();
            $table->timestamps();

            $table->foreign('idJadwalAwal')->references('id')->on('jadwalkerja')->onDelete('cascade');
            $table->foreign('idJadwalBaru')->references('id')->on('jadwalkerja')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tukar_jadwal');
    }
};
