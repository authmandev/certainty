<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CalendarClock,
    CircleCheck,
    FileCheck,
    FilePen,
    FileX,
    Users,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';
import AuditTimeline from '@/components/admin/AuditTimeline.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import { auditLog, dashboard } from '@/routes/admin';
import { index as payments } from '@/routes/admin/payments';
import { index as studies } from '@/routes/admin/studies';
import { index as users } from '@/routes/admin/users';
import type { AuditLogEntry, PaymentStatus } from '@/types';

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

const props = defineProps<{
    customers: { total: number; new: number };
    studyCounts: {
        draft: number;
        expiring: number;
        expired: number;
        finalized: number;
    };
    paymentCounts: Record<PaymentStatus, number>;
    approvedRevenue: { amount: number; currency: string };
    expiringWithinDays: number;
    recentActivity: { data: AuditLogEntry[] };
}>();

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0] ?? '');
const today = formatDate(new Date().toISOString());

const underReview = computed(() => props.paymentCounts.under_review);
const totalStudies = computed(
    () =>
        props.studyCounts.draft +
        props.studyCounts.expired +
        props.studyCounts.finalized,
);
const totalPayments = computed(() =>
    Object.values(props.paymentCounts).reduce((sum, count) => sum + count, 0),
);

type Stat = {
    label: string;
    value: string;
    caption: string;
    icon: Component;
    tone: string;
    href: ReturnType<typeof dashboard>;
    test: string;
};

const stats = computed<Stat[]>(() => [
    {
        label: 'العملاء',
        value: formatNumber(props.customers.total),
        caption: `${formatNumber(props.customers.new)} جديد خلال 30 يومًا`,
        icon: Users,
        tone: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        href: users({ query: { role: 'customer' } }),
        test: 'customers',
    },
    {
        label: 'مسودات نشطة',
        value: formatNumber(props.studyCounts.draft),
        caption: `من أصل ${formatNumber(totalStudies.value)} دراسة`,
        icon: FilePen,
        tone: 'bg-primary/10 text-primary',
        href: studies({ query: { status: 'draft' } }),
        test: 'drafts',
    },
    {
        label: 'دراسات مكتملة',
        value: formatNumber(props.studyCounts.finalized),
        caption: 'صدر تقريرها النهائي',
        icon: FileCheck,
        tone: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        href: studies({ query: { status: 'finalized' } }),
        test: 'finalized',
    },
    {
        label: 'إيرادات معتمدة',
        value: formatCurrency(
            props.approvedRevenue.amount,
            props.approvedRevenue.currency,
        ),
        caption: `${formatNumber(props.paymentCounts.approved)} دفعة مقبولة`,
        icon: Wallet,
        tone: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        href: payments({ query: { status: 'approved' } }),
        test: 'revenue',
    },
]);

type Segment = { label: string; value: number; color: string };

const studySegments = computed<Segment[]>(() => [
    { label: 'مسودات', value: props.studyCounts.draft, color: 'bg-primary' },
    {
        label: 'منتهية الصلاحية',
        value: props.studyCounts.expired,
        color: 'bg-rose-400 dark:bg-rose-500',
    },
    {
        label: 'مكتملة',
        value: props.studyCounts.finalized,
        color: 'bg-emerald-500',
    },
]);

const paymentSegments = computed<Segment[]>(() => [
    {
        label: 'بانتظار التحويل',
        value: props.paymentCounts.pending,
        color: 'bg-neutral-300 dark:bg-neutral-600',
    },
    {
        label: 'قيد المراجعة',
        value: props.paymentCounts.under_review,
        color: 'bg-amber-400',
    },
    {
        label: 'مقبولة',
        value: props.paymentCounts.approved,
        color: 'bg-emerald-500',
    },
    {
        label: 'مرفوضة',
        value: props.paymentCounts.rejected,
        color: 'bg-rose-400 dark:bg-rose-500',
    },
]);

const funnel = computed(() =>
    [
        {
            label: 'دراسات أُنشئت',
            value: totalStudies.value,
            color: 'bg-primary/50',
        },
        {
            label: 'أُرسل إيصالها',
            value: totalPayments.value - props.paymentCounts.pending,
            color: 'bg-amber-400',
        },
        {
            label: 'دفع مقبول',
            value: props.paymentCounts.approved,
            color: 'bg-emerald-500',
        },
        {
            label: 'تقرير نهائي',
            value: props.studyCounts.finalized,
            color: 'bg-primary',
        },
    ].map((step) => ({
        ...step,
        share: share(step.value, totalStudies.value),
    })),
);

function share(value: number, total: number): number {
    return total > 0 ? (value / total) * 100 : 0;
}
</script>

<template>
    <Head title="لوحة الإدارة" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Greeting -->
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-muted-foreground">{{ today }}</p>
                <h1 class="mt-1 font-display text-2xl font-bold md:text-3xl">
                    مرحبًا، {{ firstName }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    هذا ملخص نشاط المنصة اليوم.
                </p>
            </div>
        </header>

        <!-- Needs attention -->
        <section
            class="grid gap-4 lg:grid-cols-2"
            aria-label="يحتاج إلى انتباهك"
        >
            <Link
                :href="payments({ query: { status: 'under_review' } })"
                class="group relative overflow-hidden rounded-2xl border p-5 transition-shadow hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="
                    underReview > 0
                        ? 'border-amber-200 bg-gradient-to-l from-amber-50 to-card dark:border-amber-900/60 dark:from-amber-950/40'
                        : 'bg-card'
                "
                data-test="payments-under-review"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <span
                            class="flex size-12 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                underReview > 0
                                    ? 'bg-amber-500 text-white'
                                    : 'bg-muted text-muted-foreground'
                            "
                        >
                            <Banknote class="size-6" />
                        </span>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                إيصالات بانتظار المراجعة
                            </p>
                            <p
                                class="mt-1 font-display text-4xl font-bold tabular-nums"
                            >
                                {{ formatNumber(underReview) }}
                            </p>
                        </div>
                    </div>
                    <CircleCheck
                        v-if="underReview === 0"
                        class="size-5 text-emerald-500"
                    />
                </div>
                <p
                    class="mt-4 flex items-center gap-1.5 text-sm font-medium"
                    :class="
                        underReview > 0
                            ? 'text-amber-700 dark:text-amber-300'
                            : 'text-muted-foreground'
                    "
                >
                    {{
                        underReview > 0
                            ? 'مراجعة الإيصالات الآن'
                            : 'لا توجد إيصالات بانتظارك'
                    }}
                    <ArrowLeft
                        v-if="underReview > 0"
                        class="size-4 transition-transform group-hover:-translate-x-1"
                    />
                </p>
            </Link>

            <Link
                :href="studies({ query: { status: 'draft' } })"
                class="group relative overflow-hidden rounded-2xl border p-5 transition-shadow hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="
                    studyCounts.expiring > 0
                        ? 'border-sky-200 bg-gradient-to-l from-sky-50 to-card dark:border-sky-900/60 dark:from-sky-950/40'
                        : 'bg-card'
                "
                data-test="expiring"
            >
                <div class="flex items-start gap-4">
                    <span
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl"
                        :class="
                            studyCounts.expiring > 0
                                ? 'bg-sky-500 text-white'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        <CalendarClock class="size-6" />
                    </span>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            مسودات تنتهي خلال
                            {{ formatNumber(expiringWithinDays) }} أيام
                        </p>
                        <p
                            class="mt-1 font-display text-4xl font-bold tabular-nums"
                        >
                            {{ formatNumber(studyCounts.expiring) }}
                        </p>
                    </div>
                </div>
                <p
                    class="mt-4 flex items-center gap-1.5 text-sm text-muted-foreground"
                >
                    <FileX class="size-4" />
                    {{ formatNumber(studyCounts.expired) }} دراسة منتهية
                    الصلاحية قد تحتاج إلى تمديد
                </p>
            </Link>
        </section>

        <!-- Key figures -->
        <section
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="أرقام رئيسية"
        >
            <Link
                v-for="stat in stats"
                :key="stat.label"
                :href="stat.href"
                class="rounded-2xl border bg-card p-5 transition-colors hover:border-primary/40 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :data-test="stat.test"
            >
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-muted-foreground">
                        {{ stat.label }}
                    </p>
                    <span
                        class="flex size-9 items-center justify-center rounded-lg"
                        :class="stat.tone"
                    >
                        <component :is="stat.icon" class="size-4.5" />
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold tabular-nums">
                    {{ stat.value }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ stat.caption }}
                </p>
            </Link>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_400px]">
            <!-- Breakdowns -->
            <section class="space-y-6 rounded-2xl border bg-card p-5">
                <div
                    v-for="group in [
                        {
                            title: 'حالة الدراسات',
                            total: totalStudies,
                            unit: 'دراسة',
                            segments: studySegments,
                            href: studies(),
                        },
                        {
                            title: 'حالة المدفوعات',
                            total: totalPayments,
                            unit: 'دفعة',
                            segments: paymentSegments,
                            href: payments(),
                        },
                    ]"
                    :key="group.title"
                >
                    <div class="flex items-baseline justify-between gap-2">
                        <h2 class="font-semibold">{{ group.title }}</h2>
                        <Link
                            :href="group.href"
                            class="text-sm text-muted-foreground hover:text-primary"
                        >
                            {{ formatNumber(group.total) }} {{ group.unit }}
                        </Link>
                    </div>
                    <div
                        class="mt-3 flex h-3 overflow-hidden rounded-full bg-muted"
                        role="img"
                        :aria-label="
                            group.segments
                                .map(
                                    (segment) =>
                                        `${segment.label}: ${segment.value}`,
                                )
                                .join('، ')
                        "
                    >
                        <div
                            v-for="segment in group.segments"
                            :key="segment.label"
                            class="h-full transition-[width] duration-700"
                            :class="segment.color"
                            :style="{
                                width: `${share(segment.value, group.total)}%`,
                            }"
                        ></div>
                    </div>
                    <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                        <li
                            v-for="segment in group.segments"
                            :key="segment.label"
                            class="flex items-center gap-2"
                        >
                            <span
                                class="size-2.5 shrink-0 rounded-full"
                                :class="segment.color"
                            ></span>
                            <span class="text-muted-foreground">{{
                                segment.label
                            }}</span>
                            <span class="font-semibold tabular-nums">{{
                                formatNumber(segment.value)
                            }}</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-semibold">مسار الدراسات</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        من إنشاء الدراسة حتى إصدار تقريرها النهائي.
                    </p>
                    <ol class="mt-4 space-y-3" data-test="study-funnel">
                        <li
                            v-for="step in funnel"
                            :key="step.label"
                            class="grid grid-cols-[8rem_minmax(0,1fr)_3.5rem] items-center gap-3 text-sm"
                        >
                            <span class="text-muted-foreground">{{
                                step.label
                            }}</span>
                            <span
                                class="h-2.5 overflow-hidden rounded-full bg-muted"
                            >
                                <span
                                    class="block h-full rounded-full transition-[width] duration-700"
                                    :class="step.color"
                                    :style="{ width: `${step.share}%` }"
                                ></span>
                            </span>
                            <span class="text-end font-semibold tabular-nums">{{
                                formatNumber(step.value)
                            }}</span>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- Recent activity -->
            <section class="rounded-2xl border bg-card p-5">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h2 class="font-semibold">آخر العمليات</h2>
                    <Link
                        :href="auditLog()"
                        class="flex items-center gap-1 text-sm text-primary hover:underline"
                    >
                        السجل كاملًا
                        <ArrowLeft class="size-3.5" />
                    </Link>
                </div>
                <AuditTimeline
                    :entries="recentActivity.data"
                    show-study
                    compact
                />
            </section>
        </div>
    </div>
</template>
