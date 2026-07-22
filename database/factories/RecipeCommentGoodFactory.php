<?php

namespace Database\Factories;

use App\Models\RecipeComment;
use App\Models\RecipeCommentGood;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeCommentGood>
 */
class RecipeCommentGoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'comment_id' => RecipeComment::factory(),
        ];
    }
}
