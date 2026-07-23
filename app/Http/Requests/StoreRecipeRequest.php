<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'recipe_image' => ['required', 'image', 'max:5120'],
            'cooking_time' => ['required', 'integer', 'min:1'],
            'servings' => ['required', 'integer', 'min:1'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.name' => ['required', 'string', 'max:255'],
            'ingredients.*.quantity' => ['required', 'string', 'max:255'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.description' => ['required', 'string'],
            'steps.*.images' => ['nullable', 'array'],
            'steps.*.images.*' => ['image', 'max:5120'],
        ];
    }
}
