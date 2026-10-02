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
        Schema::create('game_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('console_id')->constrained('consoles')->cascadeOnDelete();
            $table->string('title');
            $table->string('region')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('votes_count')->default(1);
            $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
            $table->foreignId('completed_game_id')->nullable()->constrained('games')->nullOnDelete();
            $table->string('requester_name')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['status', 'votes_count']);
            $table->index('console_id');
        });

        Schema::create('game_request_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_request_id')->constrained('game_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_hash', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['game_request_id', 'ip_hash']);
            $table->index(['game_request_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_request_votes');
        Schema::dropIfExists('game_requests');
    }
};
