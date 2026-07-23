<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('誰でもレシピ一覧を閲覧できる', function () {
    Recipe::factory()->count(3)->create();

    $response = $this->get(route('recipes.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('recipes/Index')
        ->has('recipes.data', 3)
    );
});

test('誰でもレシピ詳細を閲覧でき閲覧数が増える', function () {
    $recipe = Recipe::factory()->create(['view_count' => 0]);

    $response = $this->get(route('recipes.show', $recipe));

    $response->assertInertia(fn ($page) => $page
        ->component('recipes/Show')
        ->where('recipe.data.id', $recipe->id)
    );
    expect($recipe->fresh()->view_count)->toBe(1);
});

test('未ログインは投稿フォームにアクセスできない', function () {
    $response = $this->get(route('recipes.create'));

    $response->assertRedirect(route('login'));
});

test('ログイン済みユーザーはレシピを投稿できる', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('recipes.store'), [
        'title' => '肉じゃが',
        'description' => '定番の肉じゃがです。',
        'recipe_image' => UploadedFile::fake()->create('recipe.jpg', 100, 'image/jpeg'),
        'cooking_time' => 30,
        'servings' => 2,
        'ingredients' => [
            ['name' => 'じゃがいも', 'quantity' => '3個'],
            ['name' => '牛肉', 'quantity' => '200g'],
        ],
        'steps' => [
            ['description' => '材料を切る', 'images' => [UploadedFile::fake()->create('step1.jpg', 100, 'image/jpeg')]],
            ['description' => '煮込む'],
        ],
    ]);

    $recipe = Recipe::first();
    $response->assertRedirect(route('recipes.show', $recipe));
    expect($recipe->user_id)->toBe($user->id)
        ->and($recipe->ingredients)->toHaveCount(2)
        ->and($recipe->steps)->toHaveCount(2)
        ->and($recipe->steps->first()->stepImages)->toHaveCount(1);
    Storage::disk('public')->assertExists($recipe->recipe_image_path);
});

test('画像がない場合はバリデーションエラーになる', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('recipes.store'), [
        'title' => '肉じゃが',
        'cooking_time' => 30,
        'servings' => 2,
        'ingredients' => [['name' => 'じゃがいも', 'quantity' => '3個']],
        'steps' => [['description' => '煮込む']],
    ]);

    $response->assertSessionHasErrors('recipe_image');
});

test('投稿者本人は編集画面にアクセスでき更新できる', function () {
    $owner = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    $editResponse = $this->actingAs($owner)->get(route('recipes.edit', $recipe));
    $editResponse->assertInertia(fn ($page) => $page->component('recipes/Edit'));

    $updateResponse = $this->actingAs($owner)->put(route('recipes.update', $recipe), [
        'title' => '更新後のタイトル',
        'cooking_time' => 45,
        'servings' => 4,
        'ingredients' => [['name' => '玉ねぎ', 'quantity' => '1個']],
        'steps' => [['description' => '炒める']],
    ]);

    $updateResponse->assertRedirect(route('recipes.show', $recipe));
    expect($recipe->fresh()->title)->toBe('更新後のタイトル')
        ->and($recipe->fresh()->ingredients)->toHaveCount(1);
});

test('投稿者以外は編集・更新できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('recipes.edit', $recipe))->assertForbidden();

    $this->actingAs($other)->put(route('recipes.update', $recipe), [
        'title' => '書き換え',
        'cooking_time' => 10,
        'servings' => 1,
        'ingredients' => [['name' => '塩', 'quantity' => '少々']],
        'steps' => [['description' => '混ぜる']],
    ])->assertForbidden();
});

test('投稿者本人はレシピを削除できる', function () {
    $owner = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    $response = $this->actingAs($owner)->delete(route('recipes.destroy', $recipe));

    $response->assertRedirect(route('recipes.index'));
    expect($recipe->fresh()->trashed())->toBeTrue();
});

test('投稿者以外はレシピを削除できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    $this->actingAs($other)->delete(route('recipes.destroy', $recipe))->assertForbidden();
});
