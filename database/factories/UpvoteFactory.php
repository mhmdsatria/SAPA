<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\Upvote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UpvoteFactory extends Factory
{
    protected $model = Upvote::class;

    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory()->approved(),
            'user_id' => User::factory(),
        ];
    }
}
