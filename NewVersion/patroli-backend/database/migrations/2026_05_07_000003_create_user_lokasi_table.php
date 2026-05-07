<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lokasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('location_id');
            $table->string('RecordOwnerID', 50);

            $table->unique(['user_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lokasi');
    }
};