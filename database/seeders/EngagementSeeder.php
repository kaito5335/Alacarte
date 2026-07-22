<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\RecipeComment;
use App\Models\RecipeCommentGood;
use App\Models\User;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $recipes = Recipe::all();

        foreach ($users as $user) {
            $favoriteRecipes = $recipes->random(fake()->numberBetween(3, 10));

            foreach ($favoriteRecipes as $recipe) {
                Favorite::create([
                    'user_id' => $user->id,
                    'recipe_id' => $recipe->id,
                ]);
            }
        }

        foreach ($recipes as $recipe) {
            $commenters = $users->random(fake()->numberBetween(0, 5));

            foreach ($commenters as $commenter) {
                $comment = RecipeComment::factory()->create([
                    'user_id' => $commenter->id,
                    'recipe_id' => $recipe->id,
                ]);

                $likers = $users->random(fake()->numberBetween(0, 8));

                foreach ($likers as $liker) {
                    RecipeCommentGood::create([
                        'user_id' => $liker->id,
                        'comment_id' => $comment->id,
                    ]);
                }
            }
        }
    }
}
