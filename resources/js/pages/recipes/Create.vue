<script setup lang="ts">
import RecipeForm from '@/components/recipes/RecipeForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'レシピ', href: '/recipes' },
    { title: '投稿', href: '/recipes/create' },
];
</script>

<template>
    <Head title="レシピを投稿" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-6 p-4">
            <h1 class="text-2xl font-semibold">レシピを投稿</h1>
            <RecipeForm submit-label="投稿する" />
        </div>
    </AppLayout>
</template>
