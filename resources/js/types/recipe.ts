import type { FormDataConvertible } from '@inertiajs/core';

export interface UserSummary {
    id: number;
    name: string;
    handle: string;
    user_image_url: string | null;
}

export interface Ingredient {
    id: number;
    order_number: number;
    name: string;
    quantity: string;
}

export interface StepImage {
    id: number;
    step_image_url: string;
}

export interface Step {
    id: number;
    order_number: number;
    description: string;
    images?: StepImage[];
}

export interface Recipe {
    id: number;
    title: string;
    description: string | null;
    recipe_image_url: string;
    cooking_time: number;
    servings: number;
    view_count: number;
    user?: UserSummary;
    ingredients?: Ingredient[];
    steps?: Step[];
    favorites_count?: number;
    comments_count?: number;
    created_at: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
}

/**
 * API Resource のコレクションを Inertia に渡したときの形。
 * ページ番号リンクの配列は links ではなく meta.links にある
 * （トップレベルの links は first / last / prev / next のオブジェクト）
 */
export interface Paginated<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}

/** フォーム入力中の材料。id を持たない点が Ingredient と異なる */
export interface IngredientInput {
    [key: string]: FormDataConvertible;
    name: string;
    quantity: string;
}

/** フォーム入力中の手順。images は新規アップロードするファイル */
export interface StepInput {
    [key: string]: FormDataConvertible;
    description: string;
    images: File[];
}

export interface RecipeFormData {
    // useForm は FormDataType（FormDataConvertible のインデックスシグネチャ）を要求する
    [key: string]: FormDataConvertible;
    title: string;
    description: string;
    recipe_image: File | null;
    cooking_time: number | string;
    servings: number | string;
    ingredients: IngredientInput[];
    steps: StepInput[];
}
