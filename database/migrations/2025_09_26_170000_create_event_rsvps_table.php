<?php

use App\Enums\RsvpStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', array_map(fn ($c) => $c->value, RsvpStatus::cases()))
                ->default(RsvpStatus::Interested->value);
            $table->unsignedInteger('waitlist_position')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'waitlist_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_rsvps');
    }
};

