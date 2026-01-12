<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('lokasi');
            $table->integer('kapasitas_terakhir');
            $table->integer('kadar_bau_terakhir');
            $table->timestamp('waktu_pengangkutan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};