<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type { Paginated, Recipe } from '@/types/recipe';
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Heart, MessageCircle, Users } from 'lucide-vue-next';

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
                <Link
                    v-for="recipe in recipes.data"
                    :key="recipe.id"
                    :href="route('recipes.show', recipe.id)"
                    class="group overflow-hidden rounded-xl border border-sidebar-border/70 transition hover:border-sidebar-border hover:shadow-sm dark:border-sidebar-border"
                >
                    <img :src="recipe.recipe_image_url" :alt="recipe.title" class="aspect-video w-full object-cover" />

                    <div class="flex flex-col gap-2 p-4">
                        <h2 class="line-clamp-1 font-medium group-hover:underline">{{ recipe.title }}</h2>
                        <p v-if="recipe.description" class="line-clamp-2 text-sm text-muted-foreground">{{ recipe.description }}</p>

                        <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1"><Clock class="h-3.5 w-3.5" />{{ recipe.cooking_time }}分</span>
                            <span class="flex items-center gap-1"><Users class="h-3.5 w-3.5" />{{ recipe.servings }}人分</span>
                            <span v-if="recipe.favorites_count !== undefined" class="flex items-center gap-1">
                                <Heart class="h-3.5 w-3.5" />{{ recipe.favorites_count }}
                            </span>
                            <span v-if="recipe.comments_count !== undefined" class="flex items-center gap-1">
                                <MessageCircle class="h-3.5 w-3.5" />{{ recipe.comments_count }}
                            </span>
                        </div>

                        <p v-if="recipe.user" class="text-xs text-muted-foreground">by {{ recipe.user.name }}</p>
                    </div>
                </Link>
            </div>

            <div v-if="recipes.meta.last_page > 1" class="flex flex-wrap justify-center gap-1">
                <template v-for="link in recipes.links" :key="link.label">
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
