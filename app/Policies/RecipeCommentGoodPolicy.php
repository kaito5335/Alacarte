<?php

namespace App\Policies;

use App\Models\RecipeCommentGood;
use App\Models\User;

class RecipeCommentGoodPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RecipeCommentGood $recipeCommentGood): bool
    {
        return $user->id === $recipeCommentGood->user_id;
    }
}
