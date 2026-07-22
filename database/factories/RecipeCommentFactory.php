<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\RecipeComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeComment>
 */
class RecipeCommentFactory extends Factory
{
    /**
     * @var list<string>
     */
    private static array $descriptions = [
        '作ってみました！とても美味しかったです。',
        '簡単なのに本格的な味で驚きました。',
        '子どもも喜んで食べてくれました。',
        '次はアレンジして作ってみたいです。',
        '味付けの参考になりました、ありがとうございます。',
        '材料が少なくて済むのが嬉しいです。',
        'また作ります！',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'recipe_id' => Recipe::factory(),
            'description' => fake()->randomElement(self::$descriptions),
        ];
    }
}
