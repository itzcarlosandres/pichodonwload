<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->string('downloadable_type', 50)->default('game')->index();
            $table->unsignedBigInteger('downloadable_id')->nullable()->index();
            $table->string('item_title')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('ip_hash', 64)->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_logs');
    }
};
