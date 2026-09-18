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
        // 1. Consoles (20 Systems)
        Schema::create('consoles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name', 20)->nullable();
            $table->enum('manufacturer', ['Sony', 'Nintendo', 'Microsoft', 'Sega', 'Arcade', 'Other'])->default('Sony');
            $table->integer('generation')->nullable();
            $table->integer('release_year')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->text('description')->nullable();
            $table->string('recommended_emulator')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        // 2. Categories / Genres
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 20)->default('#3B82F6');
            $table->timestamps();
        });

        // 3. Badges (Dynamic badges like 60 FPS, Redump, etc.)
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('text_color', 30)->default('text-emerald-400');
            $table->string('bg_color', 30)->default('bg-emerald-950/80');
            $table->string('border_color', 30)->default('border-emerald-500/30');
            $table->string('icon', 40)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Games Table
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->foreignId('console_id')->constrained('consoles')->onDelete('cascade');
            $table->string('cover_url')->nullable();
            $table->string('cover_thumb_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->longText('description')->nullable();
            $table->integer('release_year')->nullable();
            $table->string('developer')->nullable();
            $table->string('publisher')->nullable();
            $table->string('serial', 50)->nullable();
            $table->string('region', 30)->default('Global');
            $table->string('languages', 100)->default('Español, Inglés');
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('file_format', 20)->default('ISO');
            $table->string('download_url')->nullable();
            $table->string('mirror_url')->nullable();
            $table->string('crc32', 20)->nullable();
            $table->string('sha256', 80)->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->decimal('rating_average', 3, 2)->default(5.00);
            $table->unsignedInteger('rating_count')->default(0);
            $table->enum('status', ['PUBLISHED', 'DRAFT', 'ARCHIVED'])->default('PUBLISHED');
            $table->boolean('is_spotlight')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        // 5. Pivot: Game - Category
        Schema::create('game_category', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->primary(['game_id', 'category_id']);
        });

        // 6. Pivot: Game - Badge
        Schema::create('game_badge', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->primary(['game_id', 'badge_id']);
        });

        // 7. Screenshots
        Schema::create('game_screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('image_url');
            $table->string('image_webp_url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 8. Settings Table (General, Storage S3/R2, SEO, AI)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->longText('value')->nullable();
            $table->string('group', 30)->default('general'); // general, storage, seo, ai
            $table->timestamps();
        });

        // 9. Banners / Hero Promotions
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image_url');
            $table->string('target_url')->nullable();
            $table->string('badge_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 10. Reviews & Hardware Reports
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('score')->default(5);
            $table->text('comment')->nullable();
            $table->string('tested_hardware')->nullable();
            $table->string('tested_emulator')->nullable();
            $table->string('fps_performance', 30)->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
        });

        // 11. Favorites / Library
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->unique(['user_id', 'game_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('game_screenshots');
        Schema::dropIfExists('game_badge');
        Schema::dropIfExists('game_category');
        Schema::dropIfExists('games');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('consoles');
    }
};
