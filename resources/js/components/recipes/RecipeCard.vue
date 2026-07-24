<script setup lang="ts">
import type { Recipe } from '@/types/recipe';
import { Link } from '@inertiajs/vue3';
import { Clock, Heart, MessageCircle, Users } from 'lucide-vue-next';

defineProps<{
    recipe: Recipe;
}>();
</script>

<template>
    <Link
        :href="route('recipes.show', { recipe: recipe.id })"
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
</template>
