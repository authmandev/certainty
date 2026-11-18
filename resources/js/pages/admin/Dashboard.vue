<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Users } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'نظرة عامة',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    customersCount: number;
}>();
</script>

<template>
    <Head title="لوحة الإدارة" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading title="نظرة عامة" description="ملخص سريع لنشاط المنصة" />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between gap-2"
                >
                    <CardDescription>العملاء المسجلون</CardDescription>
                    <Users class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <CardTitle class="text-3xl tabular-nums">
                        {{ customersCount.toLocaleString('ar') }}
                    </CardTitle>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
