<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\Favorite;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    /**
     * お気に入り登録したレシピの一覧。
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $recipes = Recipe::query()
            ->whereIn('id', $user->favorites()->select('recipe_id'))
            ->with('user')
            ->withCount(['favorites', 'comments'])
            ->withFavoritedBy($user)
            ->latest()
            ->paginate(20);

        return Inertia::render('favorites/Index', [
            'recipes' => RecipeResource::collection($recipes),
        ]);
    }

    /**
     * お気に入りに登録する。二重登録しても1件になるよう firstOrCreate を使う。
     */
    public function store(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('create', Favorite::class);

        $request->user()->favorites()->firstOrCreate(['recipe_id' => $recipe->id]);

        return back();
    }

    /**
     * お気に入りを解除する。
     */
    public function destroy(Request $request, Recipe $recipe): RedirectResponse
    {
        $favorite = $request->user()->favorites()->where('recipe_id', $recipe->id)->first();

        if ($favorite === null) {
            return back();
        }

        $this->authorize('delete', $favorite);

        $favorite->delete();

        return back();
    }
}
