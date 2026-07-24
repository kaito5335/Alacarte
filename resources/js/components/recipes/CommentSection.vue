<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import type { RecipeComment } from '@/types/recipe';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ThumbsUp, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    recipeId: number;
    comments: RecipeComment[];
}>();

const page = usePage<SharedData>();
const currentUserId = computed(() => page.props.auth.user?.id ?? null);
const isLoggedIn = computed(() => currentUserId.value !== null);

const form = useForm({ description: '' });

const submit = () => {
    form.post(route('comments.store', { recipe: props.recipeId }), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const destroy = (comment: RecipeComment) => {
    router.delete(route('comments.destroy', { comment: comment.id }), { preserveScroll: true });
};

const toggleGood = (comment: RecipeComment) => {
    const params = { comment: comment.id };
    const options = { preserveScroll: true };

    if (comment.is_gooded) {
        router.delete(route('comment-goods.destroy', params), options);

        return;
    }

    router.post(route('comment-goods.store', params), {}, options);
};
</script>

<template>
    <section class="flex flex-col gap-4">
        <h2 class="text-lg font-medium">コメント（{{ comments.length }}）</h2>

        <form v-if="isLoggedIn" @submit.prevent="submit" class="flex flex-col gap-2">
            <textarea
                v-model="form.description"
                rows="3"
                placeholder="作ってみた感想を書いてみましょう"
                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            />
            <InputError :message="form.errors.description" />
            <Button type="submit" class="self-start" :disabled="form.processing">コメントする</Button>
        </form>

        <p v-else class="text-sm text-muted-foreground">
            コメントするには
            <Link :href="route('login')" class="underline underline-offset-4">ログイン</Link>
            してください。
        </p>

        <p v-if="comments.length === 0" class="py-6 text-center text-sm text-muted-foreground">まだコメントがありません。</p>

        <ul v-else class="flex flex-col gap-3">
            <li v-for="comment in comments" :key="comment.id" class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <div class="flex items-start justify-between gap-2">
                    <p v-if="comment.user" class="text-sm font-medium">
                        {{ comment.user.name }}
                        <span class="font-normal text-muted-foreground">@{{ comment.user.handle }}</span>
                    </p>

                    <Button
                        v-if="comment.user && comment.user.id === currentUserId"
                        variant="ghost"
                        size="icon"
                        aria-label="コメントを削除"
                        @click="destroy(comment)"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>

                <p class="mt-1 whitespace-pre-wrap text-sm">{{ comment.description }}</p>

                <div class="mt-2">
                    <Button v-if="isLoggedIn" variant="ghost" size="sm" class="gap-1.5" aria-label="いいね" @click="toggleGood(comment)">
                        <ThumbsUp class="h-3.5 w-3.5" :class="comment.is_gooded ? 'fill-current text-primary' : ''" />
                        {{ comment.goods_count }}
                    </Button>
                    <span v-else class="flex items-center gap-1.5 text-sm text-muted-foreground">
                        <ThumbsUp class="h-3.5 w-3.5" />{{ comment.goods_count }}
                    </span>
                </div>
            </li>
        </ul>
    </section>
</template>
