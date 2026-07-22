<?php

use App\Models\Recipe;
use App\Models\User;

test('誰でもレシピを閲覧できる', function () {
    $recipe = Recipe::factory()->create();

    expect((new User)->can('viewAny', Recipe::class))->toBeTrue()
        ->and($recipe->user->can('view', $recipe))->toBeTrue();
});

test('ログイン済みユーザーはレシピを投稿できる', function () {
    $user = User::factory()->create();

    expect($user->can('create', Recipe::class))->toBeTrue();
});

test('投稿者本人はレシピを編集・削除できる', function () {
    $owner = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    expect($owner->can('update', $recipe))->toBeTrue()
        ->and($owner->can('delete', $recipe))->toBeTrue();
});

test('投稿者以外はレシピを編集・削除できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    expect($other->can('update', $recipe))->toBeFalse()
        ->and($other->can('delete', $recipe))->toBeFalse();
});
