<?php

namespace App\Policies;

use App\Models\RecipeComment;
use App\Models\User;

class RecipeCommentPolicy
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
    public function delete(User $user, RecipeComment $recipeComment): bool
    {
        return $user->id === $recipeComment->user_id;
    }
}
