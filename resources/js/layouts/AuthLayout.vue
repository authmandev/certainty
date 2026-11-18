<script setup lang="ts">
import { ConfigProvider } from 'reka-ui';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <ConfigProvider dir="rtl">
        <AuthLayout :title="title" :description="description">
            <slot />
        </AuthLayout>
    </ConfigProvider>
</template>
