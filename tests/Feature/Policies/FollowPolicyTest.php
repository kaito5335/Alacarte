<?php

use App\Models\Follow;
use App\Models\User;

test('ログイン済みユーザーはフォローできる', function () {
    $user = User::factory()->create();

    expect($user->can('create', Follow::class))->toBeTrue();
});

test('フォローした本人はフォローを解除できる', function () {
    $follower = User::factory()->create();
    $follow = Follow::factory()->for($follower, 'follower')->create();

    expect($follower->can('delete', $follow))->toBeTrue();
});

test('フォローした本人以外はフォローを解除できない', function () {
    $follower = User::factory()->create();
    $other = User::factory()->create();
    $follow = Follow::factory()->for($follower, 'follower')->create();

    expect($other->can('delete', $follow))->toBeFalse();
});
