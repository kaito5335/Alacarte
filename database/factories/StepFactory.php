<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\Step;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Step>
 */
class StepFactory extends Factory
{
    /**
     * @var list<string>
     */
    private static array $descriptions = [
        '野菜は食べやすい大きさに切っておく。',
        '鍋に油を熱し、肉の色が変わるまで炒める。',
        '野菜を加えてさらに炒める。',
        '水と調味料を加え、煮立ったらアクを取る。',
        '落し蓋をして中火で15分ほど煮る。',
        '味を見て、足りなければ調味料で調える。',
        '器に盛り付けて完成。',
        'フライパンを熱し、中火で両面を焼く。',
        '下味をつけて10分ほど置く。',
        '仕上げに刻んだねぎを散らす。',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'order_number' => 1,
            'description' => fake()->randomElement(self::$descriptions),
        ];
    }
}
