<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwalkerja', function (Blueprint $table) {
            $table->unsignedBigInteger('shiftid')->nullable()->change();
            $table->string('StatusKehadiran', 20)->nullable()->after('shiftid');
            $table->string('KeteranganStatusKehadiran', 255)->nullable()->after('StatusKehadiran');
        });
    }

    public function down(): void
    {
        Schema::table('jadwalkerja', function (Blueprint $table) {
            $table->dropColumn(['StatusKehadiran', 'KeteranganStatusKehadiran']);
            $table->unsignedBigInteger('shiftid')->nullable(false)->change();
        });
    }
};