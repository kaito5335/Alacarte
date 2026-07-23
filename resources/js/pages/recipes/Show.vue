<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import type { Recipe } from '@/types/recipe';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Clock, Eye, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    recipe: { data: Recipe };
}>();

const page = usePage<SharedData>();

const recipe = computed(() => props.recipe.data);
const isOwner = computed(() => page.props.auth?.user?.id === recipe.value.user?.id);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'レシピ', href: '/recipes' },
    { title: recipe.value.title, href: route('recipes.show', recipe.value.id) },
]);

const destroy = () => {
    router.delete(route('recipes.destroy', recipe.value.id));
};
</script>

<template>
    <Head :title="recipe.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4">
            <img :src="recipe.recipe_image_url" :alt="recipe.title" class="aspect-video w-full rounded-xl object-cover" />

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-semibold">{{ recipe.title }}</h1>
                    <p v-if="recipe.user" class="text-sm text-muted-foreground">by {{ recipe.user.name }} (@{{ recipe.user.handle }})</p>
                </div>

                <div v-if="isOwner" class="flex gap-2">
                    <Button variant="secondary" as-child>
                        <Link :href="route('recipes.edit', recipe.id)">編集</Link>
                    </Button>

                    <Dialog>
                        <DialogTrigger as-child>
                            <Button variant="destructive">削除</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader class="space-y-3">
                                <DialogTitle>このレシピを削除しますか？</DialogTitle>
                                <DialogDescription>削除すると一覧から表示されなくなります。</DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button variant="secondary">キャンセル</Button>
                                </DialogClose>
                                <Button variant="destructive" @click="destroy">削除する</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                <span class="flex items-center gap-1"><Clock class="h-4 w-4" />{{ recipe.cooking_time }}分</span>
                <span class="flex items-center gap-1"><Users class="h-4 w-4" />{{ recipe.servings }}人分</span>
                <span class="flex items-center gap-1"><Eye class="h-4 w-4" />{{ recipe.view_count }}</span>
            </div>

            <p v-if="recipe.description" class="whitespace-pre-wrap">{{ recipe.description }}</p>

            <section v-if="recipe.ingredients?.length" class="flex flex-col gap-3">
                <h2 class="text-lg font-medium">材料（{{ recipe.servings }}人分）</h2>
                <ul class="divide-y rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <li v-for="ingredient in recipe.ingredients" :key="ingredient.id" class="flex justify-between px-4 py-2.5 text-sm">
                        <span>{{ ingredient.name }}</span>
                        <span class="text-muted-foreground">{{ ingredient.quantity }}</span>
                    </li>
                </ul>
            </section>

            <section v-if="recipe.steps?.length" class="flex flex-col gap-4">
                <h2 class="text-lg font-medium">作り方</h2>
                <div v-for="step in recipe.steps" :key="step.id" class="flex flex-col gap-2">
                    <div class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-sm text-primary-foreground">
                            {{ step.order_number }}
                        </span>
                        <p class="whitespace-pre-wrap pt-0.5">{{ step.description }}</p>
                    </div>

                    <div v-if="step.images?.length" class="flex flex-wrap gap-2 pl-10">
                        <img
                            v-for="image in step.images"
                            :key="image.id"
                            :src="image.step_image_url"
                            :alt="`手順${step.order_number}の画像`"
                            class="h-32 w-32 rounded-lg object-cover"
                        />
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
