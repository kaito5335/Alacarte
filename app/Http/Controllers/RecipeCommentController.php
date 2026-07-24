<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeCommentRequest;
use App\Models\Recipe;
use App\Models\RecipeComment;
use Illuminate\Http\RedirectResponse;

class RecipeCommentController extends Controller
{
    /**
     * レシピにコメントを投稿する。
     */
    public function store(StoreRecipeCommentRequest $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('create', RecipeComment::class);

        $recipe->comments()->create([
            'user_id' => $request->user()->id,
            'description' => $request->validated('description'),
        ]);

        return back();
    }

    /**
     * 自分のコメントを削除する。
     */
    public function destroy(RecipeComment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back();
    }
}
