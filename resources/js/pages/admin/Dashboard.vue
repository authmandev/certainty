<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Banknote, Users } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatNumber } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index as payments } from '@/routes/admin/payments';

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
    paymentsUnderReviewCount: number;
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
                        {{ formatNumber(customersCount) }}
                    </CardTitle>
                </CardContent>
            </Card>
            <Link
                :href="payments({ query: { status: 'under_review' } })"
                class="rounded-xl focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <Card class="h-full transition-colors hover:border-primary/50">
                    <CardHeader
                        class="flex flex-row items-center justify-between gap-2"
                    >
                        <CardDescription
                            >إيصالات بانتظار المراجعة</CardDescription
                        >
                        <Banknote class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <CardTitle
                            class="text-3xl tabular-nums"
                            :class="
                                paymentsUnderReviewCount > 0
                                    ? 'text-amber-600 dark:text-amber-400'
                                    : ''
                            "
                        >
                            {{ formatNumber(paymentsUnderReviewCount) }}
                        </CardTitle>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
