<?php

use App\Models\Recipe;
use App\Models\RecipeComment;
use App\Models\RecipeCommentGood;
use App\Models\User;

test('ログイン済みユーザーはコメントを投稿できる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($user)
        ->post(route('comments.store', $recipe), ['description' => 'おいしくできました！'])
        ->assertRedirect();

    $this->assertDatabaseHas('recipe_comments', [
        'user_id' => $user->id,
        'recipe_id' => $recipe->id,
        'description' => 'おいしくできました！',
    ]);
});

test('空のコメントはバリデーションエラーになる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($user)
        ->post(route('comments.store', $recipe), ['description' => ''])
        ->assertSessionHasErrors('description');

    expect(RecipeComment::count())->toBe(0);
});

test('1000文字を超えるコメントはバリデーションエラーになる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $this->actingAs($user)
        ->post(route('comments.store', $recipe), ['description' => str_repeat('あ', 1001)])
        ->assertSessionHasErrors('description');
});

test('投稿者本人はコメントを削除できる', function () {
    $user = User::factory()->create();
    $comment = RecipeComment::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect();

    $this->assertDatabaseMissing('recipe_comments', ['id' => $comment->id]);
});

test('いいねが付いたコメントも削除できる', function () {
    $user = User::factory()->create();
    $comment = RecipeComment::factory()->for($user)->create();
    RecipeCommentGood::factory()->count(2)->for($comment, 'comment')->create();

    $this->actingAs($user)
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect();

    $this->assertDatabaseMissing('recipe_comments', ['id' => $comment->id]);
    $this->assertDatabaseMissing('recipe_comment_goods', ['comment_id' => $comment->id]);
});

test('投稿者以外はコメントを削除できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $comment = RecipeComment::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('comments.destroy', $comment))
        ->assertForbidden();

    $this->assertDatabaseHas('recipe_comments', ['id' => $comment->id]);
});

test('未ログインではコメントを投稿できない', function () {
    $recipe = Recipe::factory()->create();

    $this->post(route('comments.store', $recipe), ['description' => 'あ'])
        ->assertRedirect(route('login'));
});

test('レシピ詳細にコメントが新しい順で渡される', function () {
    $recipe = Recipe::factory()->create();
    $old = RecipeComment::factory()->for($recipe)->create(['created_at' => now()->subDay()]);
    $new = RecipeComment::factory()->for($recipe)->create(['created_at' => now()]);

    $this->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page
            ->has('comments.data', 2)
            ->where('comments.data.0.id', $new->id)
            ->where('comments.data.1.id', $old->id)
        );
});
