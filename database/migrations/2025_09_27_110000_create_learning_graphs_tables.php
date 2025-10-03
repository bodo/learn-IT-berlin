<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_graphs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('learning_graph_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_graph_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('level')->default(0);
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });

        Schema::create('learning_graph_node_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_graph_node_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });

        Schema::create('learning_graph_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_graph_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_node_id')->constrained('learning_graph_nodes')->cascadeOnDelete();
            $table->foreignId('to_node_id')->constrained('learning_graph_nodes')->cascadeOnDelete();
            $table->string('direction', 16)->default('to');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['learning_graph_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_graph_edges');
        Schema::dropIfExists('learning_graph_node_blocks');
        Schema::dropIfExists('learning_graph_nodes');
        Schema::dropIfExists('learning_graphs');
    }
};
