<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'content' => fake()->sentence(12),
            'status' => CommentStatus::Pending,
        ];
    }

    public function approved(): self
    {
        return $this->state(function () {
            return [
                'status' => CommentStatus::Approved,
                'approved_by' => User::factory(),
                'approved_at' => now(),
            ];
        });
    }

    public function rejected(): self
    {
        return $this->state(function () {
            return [
                'status' => CommentStatus::Rejected,
                'approved_by' => User::factory(),
                'approved_at' => now(),
            ];
        });
    }
}

