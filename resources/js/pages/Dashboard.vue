<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarClock, FilePlus2, FileText } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { create, show } from '@/routes/studies';
import type { Study } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'دراساتي',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    studies: { data: Study[] };
}>();
</script>

<template>
    <Head title="دراساتي" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="دراساتي"
                description="تابع دراسات الجدوى الخاصة بك وحالة الدفع لكل منها"
            />
            <Button as-child>
                <Link :href="create()">
                    <FilePlus2 class="size-4" />
                    دراسة جديدة
                </Link>
            </Button>
        </div>

        <div
            v-if="studies.data.length === 0"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed px-6 py-16 text-center"
        >
            <div
                class="mb-4 flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary"
            >
                <FileText class="size-6" />
            </div>
            <h3 class="text-lg font-semibold">لا توجد دراسات بعد</h3>
            <p class="mt-2 max-w-sm text-sm text-muted-foreground">
                أنشئ أول دراسة جدوى لمشروعك وابدأ بإدخال بياناته. يمكنك الدفع
                لاحقًا قبل إصدار التقرير النهائي.
            </p>
            <Button class="mt-6" as-child>
                <Link :href="create()">ابدأ دراستك الأولى</Link>
            </Button>
        </div>

        <ul v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <li
                v-for="study in studies.data"
                :key="study.id"
                class="flex flex-col rounded-2xl border bg-card p-5"
                data-test="study-card"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <Link
                            :href="show(study.id)"
                            class="block truncate font-semibold hover:underline"
                            >{{ study.title }}</Link
                        >
                        <p class="truncate text-sm text-muted-foreground">
                            {{ study.project_name }}
                        </p>
                    </div>
                    <span
                        class="shrink-0 text-xs text-muted-foreground tabular-nums"
                        dir="ltr"
                        >{{ study.reference }}</span
                    >
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <StudyStatusBadge :status="study.status" />
                    <PaymentStatusBadge
                        v-if="study.payment"
                        :status="study.payment.status"
                    />
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            تاريخ الإنشاء
                        </dt>
                        <dd>{{ formatDate(study.created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">آخر تحديث</dt>
                        <dd>{{ formatDate(study.updated_at) }}</dd>
                    </div>
                </dl>

                <div
                    class="mt-auto flex items-center justify-between gap-3 border-t pt-4"
                >
                    <span
                        v-if="study.days_remaining !== null"
                        class="flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <CalendarClock class="size-4" />
                        متبقٍ {{ study.days_remaining }} يومًا
                    </span>
                    <span v-else></span>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="show(study.id)">عرض الدراسة</Link>
                    </Button>
                </div>
            </li>
        </ul>
    </div>
</template>
