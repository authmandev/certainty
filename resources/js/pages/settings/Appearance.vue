<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'إعدادات المظهر',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="إعدادات المظهر" />

    <h1 class="sr-only">إعدادات المظهر</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="إعدادات المظهر"
            description="اختر مظهر التطبيق: فاتح أو داكن أو حسب إعدادات النظام"
        />
        <AppearanceTabs />
    </div>
</template>
