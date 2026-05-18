<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->id();
            $table->string('RecordOwnerID', 50);
            $table->string('KodeKaryawan', 50);
            $table->date('TglIzinAwal');
            $table->date('TglIzinAkhir');
            $table->date('TglPencatatan');
            $table->text('KeteranganIzin')->nullable();
            $table->tinyInteger('Approval')->default(0)->comment('0=Pending, 1=Approved, 2=Rejected');
            $table->string('ApprovedBy', 100)->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->string('CatatanApproval', 500)->nullable();
            $table->dateTime('CreatedOn');
            $table->dateTime('UpdatedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
    }
};