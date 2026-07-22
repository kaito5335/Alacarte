<?php

use App\Models\RecipeComment;
use App\Models\User;

test('ログイン済みユーザーはコメントを投稿できる', function () {
    $user = User::factory()->create();

    expect($user->can('create', RecipeComment::class))->toBeTrue();
});

test('コメント投稿者本人はコメントを削除できる', function () {
    $owner = User::factory()->create();
    $comment = RecipeComment::factory()->for($owner)->create();

    expect($owner->can('delete', $comment))->toBeTrue();
});

test('コメント投稿者以外は削除できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $comment = RecipeComment::factory()->for($owner)->create();

    expect($other->can('delete', $comment))->toBeFalse();
});
