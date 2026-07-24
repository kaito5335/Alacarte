<?php

use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\User;

test('ログイン済みユーザーはお気に入りに登録できる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($user)
        ->post(route('favorites.store', $recipe))
        ->assertRedirect();

    $this->assertDatabaseHas('favorites', [
        'user_id' => $user->id,
        'recipe_id' => $recipe->id,
    ]);
});

test('二重にお気に入り登録しても1件しか作られない', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($user)->post(route('favorites.store', $recipe));
    $this->actingAs($user)->post(route('favorites.store', $recipe));

    expect(Favorite::where('user_id', $user->id)->where('recipe_id', $recipe->id)->count())->toBe(1);
});

test('お気に入りを解除できる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    Favorite::factory()->for($user)->for($recipe)->create();

    $this->actingAs($user)
        ->delete(route('favorites.destroy', $recipe))
        ->assertRedirect();

    $this->assertDatabaseMissing('favorites', [
        'user_id' => $user->id,
        'recipe_id' => $recipe->id,
    ]);
});

test('他人のお気に入りは解除されない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $recipe = Recipe::factory()->create();
    Favorite::factory()->for($owner)->for($recipe)->create();

    $this->actingAs($other)->delete(route('favorites.destroy', $recipe));

    $this->assertDatabaseHas('favorites', [
        'user_id' => $owner->id,
        'recipe_id' => $recipe->id,
    ]);
});

test('お気に入り一覧には自分がお気に入りしたレシピだけが並ぶ', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Recipe::factory()->create();
    $theirs = Recipe::factory()->create();

    Favorite::factory()->for($user)->for($mine)->create();
    Favorite::factory()->for($other)->for($theirs)->create();

    $response = $this->actingAs($user)->get(route('favorites.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('favorites/Index')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.id', $mine->id)
    );
});

test('未ログインではお気に入り操作ができない', function () {
    $recipe = Recipe::factory()->create();

    $this->post(route('favorites.store', $recipe))->assertRedirect(route('login'));
    $this->get(route('favorites.index'))->assertRedirect(route('login'));
});

test('レシピ詳細にお気に入り済みかどうかが渡される', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    Favorite::factory()->for($user)->for($recipe)->create();

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page->where('recipe.data.is_favorited', true));
});

test('お気に入りしていないレシピは is_favorited が false になる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page->where('recipe.data.is_favorited', false));
});
