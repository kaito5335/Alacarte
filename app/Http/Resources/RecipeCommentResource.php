<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeCommentResource extends JsonResource
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
            'description' => $this->description,
            'user' => new UserSummaryResource($this->whenLoaded('user')),
            'goods_count' => $this->whenCounted('goods'),
            // withGoodedBy スコープを通したときだけ含まれる
            'is_gooded' => $this->whenHas('is_gooded', fn ($value) => (bool) $value),
            'created_at' => $this->created_at,
        ];
    }
}
