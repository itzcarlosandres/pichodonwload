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
        Schema::create('bios', function (Blueprint $table) {
            $table->id();
            $table->string('system');
            $table->string('console_slug')->nullable();
            $table->foreignId('console_id')->nullable()->constrained('consoles')->nullOnDelete();
            $table->string('files');
            $table->string('version')->nullable();
            $table->string('size')->nullable();
            $table->string('format')->default('.BIN');
            $table->string('md5')->nullable();
            $table->string('sha1')->nullable();
            $table->string('emulator')->nullable();
            $table->text('download_url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('emulators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system');
            $table->string('icon')->default('cpu');
            $table->json('platforms')->nullable();
            $table->string('version')->nullable();
            $table->json('features')->nullable();
            $table->string('license')->nullable();
            $table->text('website')->nullable();
            $table->text('download_url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->string('icon')->default('sparkles');
            $table->string('color')->default('#CE2D2D');
            $table->json('search_terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('franchise_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained('franchises')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->unique(['franchise_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('franchise_game');
        Schema::dropIfExists('franchises');
        Schema::dropIfExists('emulators');
        Schema::dropIfExists('bios');
    }
};
