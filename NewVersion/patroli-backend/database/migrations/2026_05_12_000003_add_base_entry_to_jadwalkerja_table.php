<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwalkerja', function (Blueprint $table) {
            $table->unsignedBigInteger('BaseEntry')->nullable()->after('KeteranganStatusKehadiran');
            $table->string('BaseType', 20)->nullable()->after('BaseEntry');
        });
    }

    public function down(): void
    {
        Schema::table('jadwalkerja', function (Blueprint $table) {
            $table->dropColumn(['BaseEntry', 'BaseType']);
        });
    }
};