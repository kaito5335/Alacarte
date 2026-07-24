<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipeComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipe_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * recipe_comment_goods.comment_id には cascade が張られていないため、
     * コメント削除時に紐づくいいねを先に消す（残っているとFK制約違反になる）。
     */
    protected static function booted(): void
    {
        static::deleting(function (RecipeComment $comment) {
            $comment->goods()->delete();
        });
    }

    public function goods(): HasMany
    {
        return $this->hasMany(RecipeCommentGood::class, 'comment_id');
    }

    /**
     * 閲覧者がいいね済みかを is_gooded として付与する。
     * サブクエリ1本で解決するのでコメント一覧でも N+1 にならない。ゲスト（null）は常に false。
     *
     * @param  Builder<RecipeComment>  $query
     * @return Builder<RecipeComment>
     */
    public function scopeWithGoodedBy(Builder $query, ?User $user): Builder
    {
        return $query->withExists([
            'goods as is_gooded' => fn ($goods) => $goods->where('user_id', $user?->id),
        ]);
    }
}
