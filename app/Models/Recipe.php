<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'recipe_image_path',
        'cooking_time',
        'servings',
        'view_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RecipeComment::class);
    }

    /**
     * 閲覧者がお気に入り登録済みかを is_favorited として付与する。
     * サブクエリ1本で解決するので一覧でも N+1 にならない。ゲスト（null）は常に false。
     *
     * @param  Builder<Recipe>  $query
     * @return Builder<Recipe>
     */
    public function scopeWithFavoritedBy(Builder $query, ?User $user): Builder
    {
        return $query->withExists([
            'favorites as is_favorited' => fn ($favorites) => $favorites->where('user_id', $user?->id),
        ]);
    }
}
