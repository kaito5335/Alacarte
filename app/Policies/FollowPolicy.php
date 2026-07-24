<?php

namespace App\Policies;

use App\Models\Follow;
use App\Models\User;

class FollowPolicy
{
    /**
     * Determine whether the user can create models.
     * 自分自身はフォローできない。
     */
    public function create(User $user, User $target): bool
    {
        return $user->id !== $target->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Follow $follow): bool
    {
        return $user->id === $follow->follower_id;
    }
}
