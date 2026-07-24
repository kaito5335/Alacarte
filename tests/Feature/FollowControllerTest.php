<?php

use App\Models\Follow;
use App\Models\Recipe;
use App\Models\User;

test('ログイン済みユーザーは他人をフォローできる', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->post(route('follows.store', $target))
        ->assertRedirect();

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $target->id,
    ]);
});

test('二重にフォローしても1件しか作られない', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->post(route('follows.store', $target));
    $this->actingAs($user)->post(route('follows.store', $target));

    expect(Follow::where('follower_id', $user->id)->where('followed_id', $target->id)->count())->toBe(1);
});

test('自分自身はフォローできない', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('follows.store', $user))
        ->assertForbidden();

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $user->id,
    ]);
});

test('フォローを解除できる', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->followings()->attach($target);

    $this->actingAs($user)
        ->delete(route('follows.destroy', $target))
        ->assertRedirect();

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $target->id,
    ]);
});

test('他人のフォローは解除されない', function () {
    $follower = User::factory()->create();
    $other = User::factory()->create();
    $target = User::factory()->create();
    $follower->followings()->attach($target);

    $this->actingAs($other)->delete(route('follows.destroy', $target));

    $this->assertDatabaseHas('follows', [
        'follower_id' => $follower->id,
        'followed_id' => $target->id,
    ]);
});

test('未ログインではフォロー操作ができない', function () {
    $target = User::factory()->create();

    $this->post(route('follows.store', $target))->assertRedirect(route('login'));
});

test('レシピ詳細に投稿者をフォロー済みかどうかが渡される', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $recipe = Recipe::factory()->for($author)->create();

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page->where('isFollowingAuthor', false));

    $user->followings()->attach($author);

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page->where('isFollowingAuthor', true));
});

test('フォローするとTopのフォロー中タブにそのユーザーのレシピが出る', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $recipe = Recipe::factory()->for($author)->create();

    $this->actingAs($user)->post(route('follows.store', $author));

    $this->actingAs($user)
        ->get(route('home', ['tab' => 'following']))
        ->assertInertia(fn ($page) => $page
            ->has('recipes.data', 1)
            ->where('recipes.data.0.id', $recipe->id)
        );
});
