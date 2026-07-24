<?php

namespace App\Http\Controllers;

use App\Actions\Recipe\CreateRecipeAction;
use App\Actions\Recipe\UpdateRecipeAction;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeCommentResource;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Recipe::class);

        $recipes = Recipe::query()
            ->with('user')
            ->withCount(['favorites', 'comments'])
            ->withFavoritedBy($request->user())
            ->latest()
            ->paginate(20);

        return Inertia::render('recipes/Index', [
            'recipes' => RecipeResource::collection($recipes),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Recipe::class);

        return Inertia::render('recipes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request, CreateRecipeAction $action): RedirectResponse
    {
        $this->authorize('create', Recipe::class);

        $recipe = $action->handle($request->user(), $request->validated());

        return to_route('recipes.show', $recipe);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Recipe $recipe): Response
    {
        $this->authorize('view', $recipe);

        $recipe->increment('view_count');

        $recipe->load(['user', 'ingredients', 'steps.stepImages'])
            ->loadCount(['favorites', 'comments'])
            ->loadExists(['favorites as is_favorited' => fn ($favorites) => $favorites->where('user_id', $request->user()?->id)]);

        // コメントは新しい順。投稿者といいね数・いいね済みをまとめて解決する（N+1対策）
        $comments = $recipe->comments()
            ->with('user')
            ->withCount('goods')
            ->withGoodedBy($request->user())
            ->latest()
            ->get();

        return Inertia::render('recipes/Show', [
            'recipe' => new RecipeResource($recipe),
            'comments' => RecipeCommentResource::collection($comments),
            'isFollowingAuthor' => (bool) $request->user()?->followings()->whereKey($recipe->user_id)->exists(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe): Response
    {
        $this->authorize('update', $recipe);

        $recipe->load(['ingredients', 'steps.stepImages']);

        return Inertia::render('recipes/Edit', [
            'recipe' => new RecipeResource($recipe),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe, UpdateRecipeAction $action): RedirectResponse
    {
        $this->authorize('update', $recipe);

        $action->handle($recipe, $request->validated());

        return to_route('recipes.show', $recipe);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe): RedirectResponse
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return to_route('recipes.index');
    }
}
