<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory()->approved(),
            'user_id' => User::factory(),
            'hidden_by' => null,
            'content' => fake()->sentence(12),
            'is_hidden' => false,
            'hidden_at' => null,
        ];
    }
}
