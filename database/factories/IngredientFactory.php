<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * @var list<string>
     */
    private static array $names = [
        '玉ねぎ', 'にんじん', 'じゃがいも', '豚バラ肉', '鶏むね肉',
        '牛こま切れ肉', '大根', 'ほうれん草', 'キャベツ', 'なす',
        'トマト', 'ピーマン', '豆腐', '卵', '長ねぎ',
        'にんにく', '生姜', '醤油', 'みりん', '砂糖', '味噌', '塩',
    ];

    /**
     * @var list<string>
     */
    private static array $quantities = [
        '1個', '2個', '100g', '200g', '300g', '大さじ1', '大さじ2',
        '小さじ1', '小さじ1/2', '1/2本', '1本', '少々', '適量', '1丁',
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
            'name' => fake()->randomElement(self::$names),
            'quantity' => fake()->randomElement(self::$quantities),
        ];
    }
}
