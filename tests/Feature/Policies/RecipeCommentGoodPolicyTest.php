<?php

use App\Models\RecipeCommentGood;
use App\Models\User;

test('ログイン済みユーザーはコメントにいいねできる', function () {
    $user = User::factory()->create();

    expect($user->can('create', RecipeCommentGood::class))->toBeTrue();
});

test('いいねした本人は取り消せる', function () {
    $owner = User::factory()->create();
    $good = RecipeCommentGood::factory()->for($owner)->create();

    expect($owner->can('delete', $good))->toBeTrue();
});

test('いいねした本人以外は取り消せない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $good = RecipeCommentGood::factory()->for($owner)->create();

    expect($other->can('delete', $good))->toBeFalse();
});
