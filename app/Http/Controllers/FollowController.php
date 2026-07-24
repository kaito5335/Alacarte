<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * ユーザーをフォローする。二重フォローしても1件になるよう firstOrCreate を使う。
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $this->authorize('create', [Follow::class, $user]);

        Follow::firstOrCreate([
            'follower_id' => $request->user()->id,
            'followed_id' => $user->id,
        ]);

        return back();
    }

    /**
     * フォローを解除する。
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $follow = Follow::where('follower_id', $request->user()->id)
            ->where('followed_id', $user->id)
            ->first();

        if ($follow === null) {
            return back();
        }

        $this->authorize('delete', $follow);

        $follow->delete();

        return back();
    }
}
