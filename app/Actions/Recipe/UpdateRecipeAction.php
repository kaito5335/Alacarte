<?php

namespace App\Actions\Recipe;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateRecipeAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Recipe $recipe, array $data): Recipe
    {
        return DB::transaction(function () use ($recipe, $data) {
            $recipeImagePath = $recipe->recipe_image_path;

            if (isset($data['recipe_image'])) {
                Storage::disk('public')->delete($recipeImagePath);
                $recipeImagePath = $data['recipe_image']->store('recipes', 'public');
            }

            $recipe->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'recipe_image_path' => $recipeImagePath,
                'cooking_time' => $data['cooking_time'],
                'servings' => $data['servings'],
            ]);

            $recipe->load('steps.stepImages');
            $recipe->steps->pluck('stepImages')->flatten()->each(
                fn ($stepImage) => Storage::disk('public')->delete($stepImage->step_image_path)
            );

            $recipe->ingredients()->delete();
            $recipe->steps()->delete();

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
