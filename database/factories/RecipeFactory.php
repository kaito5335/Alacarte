<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Database\Factories\Support\PlaceholderImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * @var list<string>
     */
    private static array $titles = [
        '基本の肉じゃが',
        'ふわとろオムライス',
        '鶏むね肉の唐揚げ',
        '豚バラ大根の煮物',
        '簡単チキンカレー',
        '和風だしの親子丼',
        'トマトとツナのパスタ',
        'ほうれん草のナムル',
        '鮭のホイル焼き',
        '野菜たっぷり味噌汁',
        'なすの煮浸し',
        '豆腐ハンバーグ',
        '鶏むね肉の照り焼き',
        'きんぴらごぼう',
        '簡単チャーハン',
        'ぶり大根',
        'ミネストローネ',
        '豚しゃぶサラダ',
        '厚揚げの煮物',
        '手作り餃子',
    ];

    /**
     * @var list<string>
     */
    private static array $descriptions = [
        '忙しい日でも短時間で作れる定番おかずです。',
        '子どもから大人まで喜ぶ味付けにしました。',
        '冷めても美味しいのでお弁当にもおすすめです。',
        '家にある材料で気軽に作れるレシピです。',
        '和食の基本を押さえたやさしい味付けです。',
        '野菜がたっぷり摂れる一品です。',
        '週末の作り置きにもぴったりです。',
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
            'title' => fake()->randomElement(self::$titles),
            'description' => fake()->optional(0.8)->randomElement(self::$descriptions),
            'recipe_image_path' => PlaceholderImage::store('recipes', 'Recipe'),
            'cooking_time' => fake()->numberBetween(5, 120),
            'servings' => fake()->numberBetween(1, 6),
            'view_count' => fake()->numberBetween(0, 500),
        ];
    }
}
