<?php

namespace App\Http\Controllers;

use App\Models\RecipeComment;
use App\Models\RecipeCommentGood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentGoodController extends Controller
{
    /**
     * コメントにいいねする。二重でも1件になるよう firstOrCreate を使う。
     */
    public function store(Request $request, RecipeComment $comment): RedirectResponse
    {
        $this->authorize('create', RecipeCommentGood::class);

        $comment->goods()->firstOrCreate(['user_id' => $request->user()->id]);

        return back();
    }

    /**
     * いいねを取り消す。
     */
    public function destroy(Request $request, RecipeComment $comment): RedirectResponse
    {
        $good = $comment->goods()->where('user_id', $request->user()->id)->first();

        if ($good === null) {
            return back();
        }

        $this->authorize('delete', $good);

        $good->delete();

        return back();
    }
}
