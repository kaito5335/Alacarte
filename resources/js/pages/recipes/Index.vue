<script setup lang="ts">
import RecipeCard from '@/components/recipes/RecipeCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type { Paginated, Recipe } from '@/types/recipe';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    recipes: Paginated<Recipe>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'レシピ', href: '/recipes' }];
</script>

<template>
    <Head title="レシピ一覧" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">レシピ</h1>
                <Button as-child>
                    <Link :href="route('recipes.create')">レシピを投稿</Link>
                </Button>
            </div>

            <p v-if="recipes.data.length === 0" class="py-16 text-center text-muted-foreground">まだレシピがありません。</p>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <RecipeCard v-for="recipe in recipes.data" :key="recipe.id" :recipe="recipe" />
            </div>

            <div v-if="recipes.meta.last_page > 1" class="flex flex-wrap justify-center gap-1">
                <template v-for="link in recipes.meta.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md border px-3 py-1.5 text-sm"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-accent'"
                        v-html="link.label"
                    />
                    <span v-else class="rounded-md border px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
