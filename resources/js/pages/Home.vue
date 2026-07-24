<script setup lang="ts">
import RecipeCard from '@/components/recipes/RecipeCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type SharedData } from '@/types';
import type { Feed, Recipe } from '@/types/recipe';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Shuffle } from 'lucide-vue-next';
import { computed } from 'vue';

type Tab = 'all' | 'following';

const props = defineProps<{
    recipes: Feed<Recipe>;
    tab: Tab;
}>();

const page = usePage<SharedData>();
const isLoggedIn = computed(() => page.props.auth.user !== null);
const isEmpty = computed(() => props.recipes.data.length === 0);

/** タブ切替もシャッフルも、フィード部分だけを取り直す（全データ再取得しない） */
const loadFeed = (tab: Tab) => {
    router.get(
        route('home'),
        { tab },
        {
            only: ['recipes', 'tab'],
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const tabs: { key: Tab; label: string }[] = [
    { key: 'all', label: 'みんなのレシピ' },
    { key: 'following', label: 'フォロー中' },
];
</script>

<template>
    <Head title="ホーム" />

    <AppLayout>
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">今日は何を作る？</h1>
                <p class="mt-1 text-sm text-muted-foreground">気になるレシピを眺めて決めましょう。</p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sidebar-border/70">
                <div class="flex">
                    <button
                        v-for="item in tabs"
                        :key="item.key"
                        type="button"
                        class="-mb-px border-b-2 px-4 py-2 text-sm transition"
                        :class="
                            tab === item.key
                                ? 'border-primary font-medium text-foreground'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        "
                        @click="loadFeed(item.key)"
                    >
                        {{ item.label }}
                    </button>
                </div>

                <Button v-if="tab === 'all'" variant="secondary" size="sm" class="mb-2" @click="loadFeed('all')">
                    <Shuffle class="h-4 w-4" />
                    別のレシピを見る
                </Button>
            </div>

            <!-- フォロー中タブの空状態は理由によって出し分ける -->
            <div v-if="isEmpty && tab === 'following' && !isLoggedIn" class="flex flex-col items-center gap-3 py-16 text-center">
                <p class="text-muted-foreground">ログインすると、フォロー中のユーザーのレシピが並びます。</p>
                <Button as-child>
                    <Link :href="route('login')">ログイン</Link>
                </Button>
            </div>

            <div v-else-if="isEmpty && tab === 'following'" class="flex flex-col items-center gap-3 py-16 text-center">
                <p class="text-muted-foreground">フォロー中のユーザーのレシピがまだありません。</p>
                <Button variant="secondary" @click="loadFeed('all')">みんなのレシピを見る</Button>
            </div>

            <p v-else-if="isEmpty" class="py-16 text-center text-muted-foreground">まだレシピがありません。</p>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <RecipeCard v-for="recipe in recipes.data" :key="recipe.id" :recipe="recipe" />
            </div>

            <!-- ページネーションはフォロー中タブのみ（みんなのレシピはシャッフルで引き直す） -->
            <div v-if="recipes.meta && recipes.meta.last_page > 1" class="flex flex-wrap justify-center gap-1">
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
