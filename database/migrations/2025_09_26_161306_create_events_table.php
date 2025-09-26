<?php

use App\Enums\EventStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('place');
            $table->dateTimeTz('event_datetime');
            $table->string('timezone');
            $table->unsignedInteger('max_spots')->nullable();
            $table->unsignedInteger('reserved_spots')->default(0);
            $table->enum('status', array_map(fn ($case) => $case->value, EventStatus::cases()))->default(EventStatus::Draft->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
