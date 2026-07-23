<?php

namespace App\Actions\Recipe;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateRecipeAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): Recipe
    {
        return DB::transaction(function () use ($user, $data) {
            $recipe = Recipe::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'recipe_image_path' => $data['recipe_image']->store('recipes', 'public'),
                'cooking_time' => $data['cooking_time'],
                'servings' => $data['servings'],
            ]);

            $recipe->ingredients()->createMany(
                collect($data['ingredients'])->values()->map(fn (array $ingredient, int $index) => [
                    'order_number' => $index + 1,
                    'name' => $ingredient['name'],
                    'quantity' => $ingredient['quantity'],
                ])->all()
            );

            foreach (collect($data['steps'])->values() as $index => $step) {
                $createdStep = $recipe->steps()->create([
                    'order_number' => $index + 1,
                    'description' => $step['description'],
                ]);

                $createdStep->stepImages()->createMany(
                    collect($step['images'] ?? [])->map(fn ($image) => [
                        'step_image_path' => $image->store('steps', 'public'),
                    ])->all()
                );
            }

            return $recipe;
        });
    }
}
