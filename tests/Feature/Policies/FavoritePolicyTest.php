<?php

use App\Models\Favorite;
use App\Models\User;

test('ログイン済みユーザーはお気に入り登録できる', function () {
    $user = User::factory()->create();

    expect($user->can('create', Favorite::class))->toBeTrue();
});

test('お気に入り登録した本人は解除できる', function () {
    $owner = User::factory()->create();
    $favorite = Favorite::factory()->for($owner)->create();

    expect($owner->can('delete', $favorite))->toBeTrue();
});

test('お気に入り登録した本人以外は解除できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $favorite = Favorite::factory()->for($owner)->create();

    expect($other->can('delete', $favorite))->toBeFalse();
});
