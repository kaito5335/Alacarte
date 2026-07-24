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
import { Clock, Eye, Heart, UserCheck, UserPlus, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    recipe: { data: Recipe };
    isFollowingAuthor: boolean;
}>();

const page = usePage<SharedData>();

const recipe = computed(() => props.recipe.data);
const isLoggedIn = computed(() => page.props.auth.user !== null);
const isOwner = computed(() => page.props.auth?.user?.id === recipe.value.user?.id);

/** お気に入りのトグル。押した位置を保ったまま再描画する */
const toggleFavorite = () => {
    const params = { recipe: recipe.value.id };
    const options = { preserveScroll: true };

    if (recipe.value.is_favorited) {
        router.delete(route('favorites.destroy', params), options);

        return;
    }

    router.post(route('favorites.store', params), {}, options);
};

/** 投稿者のフォローをトグルする。自分のレシピとゲストには出さない */
const canFollowAuthor = computed(() => isLoggedIn.value && !isOwner.value && recipe.value.user !== undefined);

const toggleFollow = () => {
    const author = recipe.value.user;

    if (author === undefined) {
        return;
    }

    const params = { user: author.id };
    const options = { preserveScroll: true };

    if (props.isFollowingAuthor) {
        router.delete(route('follows.destroy', params), options);

        return;
    }

    router.post(route('follows.store', params), {}, options);
};

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'レシピ', href: '/recipes' },
    { title: recipe.value.title, href: route('recipes.show', { recipe: recipe.value.id }) },
]);

const destroy = () => {
    router.delete(route('recipes.destroy', { recipe: recipe.value.id }));
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
                    <div v-if="recipe.user" class="flex flex-wrap items-center gap-2">
                        <p class="text-sm text-muted-foreground">by {{ recipe.user.name }} (@{{ recipe.user.handle }})</p>
                        <Button
                            v-if="canFollowAuthor"
                            :variant="isFollowingAuthor ? 'secondary' : 'outline'"
                            size="sm"
                            class="gap-1.5"
                            @click="toggleFollow"
                        >
                            <UserCheck v-if="isFollowingAuthor" class="h-3.5 w-3.5" />
                            <UserPlus v-else class="h-3.5 w-3.5" />
                            {{ isFollowingAuthor ? 'フォロー中' : 'フォロー' }}
                        </Button>
                    </div>
                </div>

                <div v-if="isOwner" class="flex gap-2">
                    <Button variant="secondary" as-child>
                        <Link :href="route('recipes.edit', { recipe: recipe.id })">編集</Link>
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

                <Button v-if="isLoggedIn" variant="ghost" size="sm" class="gap-1.5" @click="toggleFavorite">
                    <Heart class="h-4 w-4" :class="recipe.is_favorited ? 'fill-red-500 text-red-500' : ''" />
                    {{ recipe.is_favorited ? 'お気に入り解除' : 'お気に入り' }}
                    <span>({{ recipe.favorites_count }})</span>
                </Button>
                <span v-else class="flex items-center gap-1"><Heart class="h-4 w-4" />{{ recipe.favorites_count }}</span>
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
