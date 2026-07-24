<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'recipe_image_url' => Storage::url($this->recipe_image_path),
            'cooking_time' => $this->cooking_time,
            'servings' => $this->servings,
            'view_count' => $this->view_count,
            'user' => new UserSummaryResource($this->whenLoaded('user')),
            'ingredients' => IngredientResource::collection($this->whenLoaded('ingredients')),
            'steps' => StepResource::collection($this->whenLoaded('steps')),
            'favorites_count' => $this->whenCounted('favorites'),
            'comments_count' => $this->whenCounted('comments'),
            // withFavoritedBy スコープを通したときだけ含まれる
            'is_favorited' => $this->whenHas('is_favorited', fn ($value) => (bool) $value),
            'created_at' => $this->created_at,
        ];
    }
}
