<?php

use App\Models\Recipe;
use App\Models\RecipeComment;
use App\Models\RecipeCommentGood;
use App\Models\User;

test('ログイン済みユーザーはコメントにいいねできる', function () {
    $user = User::factory()->create();
    $comment = RecipeComment::factory()->create();

    $this->actingAs($user)
        ->post(route('comment-goods.store', $comment))
        ->assertRedirect();

    $this->assertDatabaseHas('recipe_comment_goods', [
        'user_id' => $user->id,
        'comment_id' => $comment->id,
    ]);
});

test('二重にいいねしても1件しか作られない', function () {
    $user = User::factory()->create();
    $comment = RecipeComment::factory()->create();

    $this->actingAs($user)->post(route('comment-goods.store', $comment));
    $this->actingAs($user)->post(route('comment-goods.store', $comment));

    expect(RecipeCommentGood::where('user_id', $user->id)->where('comment_id', $comment->id)->count())->toBe(1);
});

test('いいねを取り消せる', function () {
    $user = User::factory()->create();
    $comment = RecipeComment::factory()->create();
    RecipeCommentGood::factory()->for($user)->for($comment, 'comment')->create();

    $this->actingAs($user)
        ->delete(route('comment-goods.destroy', $comment))
        ->assertRedirect();

    $this->assertDatabaseMissing('recipe_comment_goods', [
        'user_id' => $user->id,
        'comment_id' => $comment->id,
    ]);
});

test('他人のいいねは取り消されない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $comment = RecipeComment::factory()->create();
    RecipeCommentGood::factory()->for($owner)->for($comment, 'comment')->create();

    $this->actingAs($other)->delete(route('comment-goods.destroy', $comment));

    $this->assertDatabaseHas('recipe_comment_goods', [
        'user_id' => $owner->id,
        'comment_id' => $comment->id,
    ]);
});

test('未ログインではいいねできない', function () {
    $comment = RecipeComment::factory()->create();

    $this->post(route('comment-goods.store', $comment))->assertRedirect(route('login'));
});

test('レシピ詳細にいいね数といいね済みかどうかが渡される', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $comment = RecipeComment::factory()->for($recipe)->create();
    RecipeCommentGood::factory()->for($user)->for($comment, 'comment')->create();

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page
            ->where('comments.data.0.goods_count', 1)
            ->where('comments.data.0.is_gooded', true)
        );
});

test('いいねしていないコメントは is_gooded が false になる', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    RecipeComment::factory()->for($recipe)->create();

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertInertia(fn ($page) => $page->where('comments.data.0.is_gooded', false));
});
