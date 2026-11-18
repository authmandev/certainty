<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();

/**
 * Shared pages such as settings keep the admin panel chrome for administrators.
 */
const layout = computed(() =>
    page.props.auth.user?.role === 'admin' ? AdminLayout : AppSidebarLayout,
);
</script>

<template>
    <component :is="layout" :breadcrumbs="breadcrumbs">
        <slot />
    </component>
</template>
