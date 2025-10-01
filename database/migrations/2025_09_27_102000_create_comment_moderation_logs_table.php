<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['comment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_moderation_logs');
    }
};

