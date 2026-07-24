<?php

use App\Models\Recipe;
use App\Models\User;

test('ゲストでもTopページのみんなのレシピが表示される', function () {
    Recipe::factory()->count(3)->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->where('tab', 'all')
        ->has('recipes.data', 3)
    );
});

test('みんなのレシピは12件までしか返さない', function () {
    Recipe::factory()->count(15)->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn ($page) => $page->has('recipes.data', 12));
});

test('フォロー中タブはフォローしているユーザーのレシピだけを返す', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create();
    $stranger = User::factory()->create();

    $user->followings()->attach($followed);

    $followedRecipe = Recipe::factory()->for($followed)->create();
    $strangerRecipe = Recipe::factory()->for($stranger)->create();

    $response = $this->actingAs($user)->get(route('home', ['tab' => 'following']));

    $response->assertInertia(fn ($page) => $page
        ->component('Home')
        ->where('tab', 'following')
        ->has('recipes.data', 1)
        ->where('recipes.data.0.id', $followedRecipe->id)
    );

    expect($strangerRecipe->user_id)->not->toBe($followed->id);
});

test('誰もフォローしていなければフォロー中タブは空になる', function () {
    $user = User::factory()->create();
    Recipe::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('home', ['tab' => 'following']));

    $response->assertInertia(fn ($page) => $page->has('recipes.data', 0));
});

test('ゲストのフォロー中タブは空で返る', function () {
    Recipe::factory()->count(3)->create();

    $response = $this->get(route('home', ['tab' => 'following']));

    $response->assertInertia(fn ($page) => $page
        ->where('tab', 'following')
        ->has('recipes.data', 0)
    );
});

test('不正なtabパラメータはみんなのレシピにフォールバックする', function () {
    Recipe::factory()->count(2)->create();

    $response = $this->get(route('home', ['tab' => 'unknown']));

    $response->assertInertia(fn ($page) => $page
        ->where('tab', 'all')
        ->has('recipes.data', 2)
    );
});
