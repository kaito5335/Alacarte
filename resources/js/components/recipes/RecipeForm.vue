<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Recipe, RecipeFormData } from '@/types/recipe';
import { useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    /** 指定時は編集モードになる */
    recipe?: Recipe;
    submitLabel: string;
}>();

const isEdit = computed(() => props.recipe !== undefined);

const form = useForm<RecipeFormData>({
    title: props.recipe?.title ?? '',
    description: props.recipe?.description ?? '',
    recipe_image: null,
    cooking_time: props.recipe?.cooking_time ?? '',
    servings: props.recipe?.servings ?? '',
    ingredients: props.recipe?.ingredients?.map((ingredient) => ({ name: ingredient.name, quantity: ingredient.quantity })) ?? [
        { name: '', quantity: '' },
    ],
    steps: props.recipe?.steps?.map((step) => ({ description: step.description, images: [] })) ?? [{ description: '', images: [] }],
});

// ingredients.0.name のようなネストしたキーは useForm の errors 型に含まれないため、
// Record として引き直す
const errors = computed(() => form.errors as Record<string, string | undefined>);

const addIngredient = () => form.ingredients.push({ name: '', quantity: '' });
const removeIngredient = (index: number) => form.ingredients.splice(index, 1);

const addStep = () => form.steps.push({ description: '', images: [] });
const removeStep = (index: number) => form.steps.splice(index, 1);

const onRecipeImageChange = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    form.recipe_image = files?.[0] ?? null;
};

const onStepImagesChange = (event: Event, index: number) => {
    const files = (event.target as HTMLInputElement).files;
    form.steps[index].images = files ? Array.from(files) : [];
};

const submit = () => {
    if (isEdit.value && props.recipe) {
        // multipart で送るため PUT ではなく POST + _method で送信する
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('recipes.update', { recipe: props.recipe.id }));

        return;
    }

    form.post(route('recipes.store'));
};
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-8">
        <div class="flex flex-col gap-4">
            <div class="grid gap-2">
                <Label for="title">タイトル</Label>
                <Input id="title" type="text" v-model="form.title" placeholder="肉じゃが" />
                <InputError :message="form.errors.title" />
            </div>

            <div class="grid gap-2">
                <Label for="description">説明</Label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    placeholder="どんなレシピか一言で"
                />
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid gap-2">
                <Label for="recipe_image">完成写真</Label>
                <img v-if="recipe" :src="recipe.recipe_image_url" alt="現在の完成写真" class="h-40 w-40 rounded-lg object-cover" />
                <p v-if="recipe" class="text-xs text-muted-foreground">変更する場合のみ選択してください。</p>
                <Input id="recipe_image" type="file" accept="image/*" @change="onRecipeImageChange" />
                <InputError :message="form.errors.recipe_image" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="cooking_time">調理時間（分）</Label>
                    <Input id="cooking_time" type="number" min="1" v-model="form.cooking_time" />
                    <InputError :message="form.errors.cooking_time" />
                </div>

                <div class="grid gap-2">
                    <Label for="servings">何人分</Label>
                    <Input id="servings" type="number" min="1" v-model="form.servings" />
                    <InputError :message="form.errors.servings" />
                </div>
            </div>
        </div>

        <section class="flex flex-col gap-3">
            <h2 class="text-lg font-medium">材料</h2>
            <InputError :message="form.errors.ingredients" />

            <div v-for="(ingredient, index) in form.ingredients" :key="index" class="flex flex-col gap-1">
                <div class="flex gap-2">
                    <div class="flex-1">
                        <Input type="text" v-model="ingredient.name" placeholder="じゃがいも" />
                        <InputError :message="errors[`ingredients.${index}.name`]" />
                    </div>
                    <div class="w-32">
                        <Input type="text" v-model="ingredient.quantity" placeholder="3個" />
                        <InputError :message="errors[`ingredients.${index}.quantity`]" />
                    </div>
                    <Button type="button" variant="ghost" size="icon" :disabled="form.ingredients.length === 1" @click="removeIngredient(index)">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <Button type="button" variant="secondary" class="self-start" @click="addIngredient">
                <Plus class="h-4 w-4" />
                材料を追加
            </Button>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="text-lg font-medium">作り方</h2>
            <InputError :message="form.errors.steps" />
            <p v-if="recipe" class="text-xs text-muted-foreground">画像を選び直さない手順は、更新時に画像なしになります。</p>

            <div v-for="(step, index) in form.steps" :key="index" class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">手順 {{ index + 1 }}</span>
                    <Button type="button" variant="ghost" size="icon" :disabled="form.steps.length === 1" @click="removeStep(index)">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>

                <textarea
                    v-model="step.description"
                    rows="2"
                    class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    placeholder="材料を切る"
                />
                <InputError :message="errors[`steps.${index}.description`]" />

                <Input type="file" accept="image/*" multiple @change="(event: Event) => onStepImagesChange(event, index)" />
                <InputError :message="errors[`steps.${index}.images`]" />
            </div>

            <Button type="button" variant="secondary" class="self-start" @click="addStep">
                <Plus class="h-4 w-4" />
                手順を追加
            </Button>
        </section>

        <Button type="submit" class="self-start" :disabled="form.processing">{{ submitLabel }}</Button>
    </form>
</template>
