<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * 「みんなのレシピ」で一度に見せる件数。
     * ランダム表示なのでページ送りはせず、シャッフルで引き直す。
     */
    private const RANDOM_FEED_LIMIT = 12;

    private const FOLLOWING_FEED_PER_PAGE = 12;

    /**
     * Topページ。「みんなのレシピ」と「フォロー中」の2フィードを切り替える。
     */
    public function index(Request $request): Response
    {
        $tab = $request->query('tab') === 'following' ? 'following' : 'all';

        return Inertia::render('Home', [
            'tab' => $tab,
            'recipes' => $tab === 'following'
                ? $this->followingFeed($request)
                : $this->randomFeed($request),
        ]);
    }

    /**
     * みんなのレシピ。毎回ランダムに引き直す。
     */
    private function randomFeed(Request $request): AnonymousResourceCollection
    {
        $recipes = $this->baseQuery($request)
            ->inRandomOrder()
            ->limit(self::RANDOM_FEED_LIMIT)
            ->get();

        return RecipeResource::collection($recipes);
    }

    /**
     * フォロー中ユーザーのレシピ。新しい順。ゲストには何も返さない。
     */
    private function followingFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user === null) {
            return RecipeResource::collection(collect());
        }

        $recipes = $this->baseQuery($request)
            ->whereIn('user_id', $user->followings()->select('users.id'))
            ->latest()
            ->paginate(self::FOLLOWING_FEED_PER_PAGE)
            ->withQueryString();

        return RecipeResource::collection($recipes);
    }

    /**
     * カード表示に必要なものだけを eager load する（N+1対策）。
     *
     * @return Builder<Recipe>
     */
    private function baseQuery(Request $request): Builder
    {
        return Recipe::query()
            ->with('user')
            ->withCount(['favorites', 'comments'])
            ->withFavoritedBy($request->user());
    }
}
