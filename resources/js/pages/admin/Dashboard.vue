<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CalendarClock,
    CircleCheck,
    FileCheck,
    FilePen,
    Sparkles,
    Users,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';
import AuditTimeline from '@/components/admin/AuditTimeline.vue';
import AnimatedNumber from '@/components/landing/AnimatedNumber.vue';
import DonutChart from '@/components/landing/DonutChart.vue';
import Reveal from '@/components/landing/Reveal.vue';
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

/**
 * The currency symbol on its own, so the amount itself can count up.
 */
const currencySymbol = computed(() =>
    formatCurrency(0, props.approvedRevenue.currency, 0)
        .replace(/[\d\s\u200e\u200f]/g, '')
        .trim(),
);

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
    value: number;
    caption: string;
    icon: Component;
    /** Icon chip gradient and the matching corner glow. */
    chip: string;
    glow: string;
    href: ReturnType<typeof dashboard>;
    money?: boolean;
    test: string;
};

const stats = computed<Stat[]>(() => [
    {
        label: 'العملاء',
        value: props.customers.total,
        caption: `${formatNumber(props.customers.new)} جديد خلال 30 يومًا`,
        icon: Users,
        chip: 'from-sky-400 to-sky-600 shadow-sky-500/30',
        glow: 'bg-sky-500/20',
        href: users({ query: { role: 'customer' } }),
        test: 'customers',
    },
    {
        label: 'مسودات نشطة',
        value: props.studyCounts.draft,
        caption: `من أصل ${formatNumber(totalStudies.value)} دراسة`,
        icon: FilePen,
        chip: 'from-teal-400 to-primary shadow-primary/30',
        glow: 'bg-primary/20',
        href: studies({ query: { status: 'draft' } }),
        test: 'drafts',
    },
    {
        label: 'دراسات مكتملة',
        value: props.studyCounts.finalized,
        caption: 'صدر تقريرها النهائي',
        icon: FileCheck,
        chip: 'from-emerald-400 to-emerald-600 shadow-emerald-500/30',
        glow: 'bg-emerald-500/20',
        href: studies({ query: { status: 'finalized' } }),
        test: 'finalized',
    },
    {
        label: 'إيرادات معتمدة',
        value: props.approvedRevenue.amount,
        caption: `${formatNumber(props.paymentCounts.approved)} دفعة مقبولة`,
        icon: Wallet,
        chip: 'from-amber-300 to-amber-500 shadow-amber-500/30',
        glow: 'bg-amber-500/20',
        href: payments({ query: { status: 'approved' } }),
        money: true,
        test: 'revenue',
    },
]);

type Segment = { label: string; value: number; color: string };

const breakdowns = computed(() => [
    {
        title: 'حالة الدراسات',
        total: totalStudies.value,
        unit: 'دراسة',
        href: studies(),
        segments: [
            {
                label: 'مسودات',
                value: props.studyCounts.draft,
                color: 'var(--primary)',
            },
            {
                label: 'منتهية الصلاحية',
                value: props.studyCounts.expired,
                color: 'oklch(0.7 0.17 15)',
            },
            {
                label: 'مكتملة',
                value: props.studyCounts.finalized,
                color: 'oklch(0.7 0.15 160)',
            },
        ] satisfies Segment[],
    },
    {
        title: 'حالة المدفوعات',
        total: totalPayments.value,
        unit: 'دفعة',
        href: payments(),
        segments: [
            {
                label: 'بانتظار التحويل',
                value: props.paymentCounts.pending,
                color: 'oklch(0.72 0.02 250)',
            },
            {
                label: 'قيد المراجعة',
                value: props.paymentCounts.under_review,
                color: 'var(--gold)',
            },
            {
                label: 'مقبولة',
                value: props.paymentCounts.approved,
                color: 'oklch(0.7 0.15 160)',
            },
            {
                label: 'مرفوضة',
                value: props.paymentCounts.rejected,
                color: 'oklch(0.7 0.17 15)',
            },
        ] satisfies Segment[],
    },
]);

const funnel = computed(() =>
    [
        { label: 'دراسات أُنشئت', value: totalStudies.value },
        {
            label: 'أُرسل إيصالها',
            value: totalPayments.value - props.paymentCounts.pending,
        },
        { label: 'دفع مقبول', value: props.paymentCounts.approved },
        { label: 'تقرير نهائي', value: props.studyCounts.finalized },
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
        <!-- Hero: greeting and what needs attention -->
        <Reveal
            as="section"
            class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-bl from-[oklch(0.3_0.06_215)] via-[oklch(0.38_0.08_205)] to-[oklch(0.46_0.1_190)] p-6 text-white shadow-xl shadow-primary/10 md:p-8 dark:from-[oklch(0.22_0.04_220)] dark:via-[oklch(0.27_0.06_210)] dark:to-[oklch(0.33_0.08_195)]"
        >
            <div
                class="pointer-events-none absolute inset-0 -z-10"
                aria-hidden="true"
            >
                <div
                    class="bg-grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_right,black,transparent_70%)] opacity-40 [--foreground:white]"
                ></div>
                <div
                    class="absolute end-[-6rem] -top-24 size-80 animate-aurora rounded-full bg-gold/30 blur-3xl"
                ></div>
                <div
                    class="absolute start-[-4rem] -bottom-32 size-96 animate-aurora rounded-full bg-teal-300/25 blur-3xl [animation-delay:-8s]"
                ></div>
            </div>

            <div
                class="grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]"
            >
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium ring-1 ring-white/20 backdrop-blur"
                    >
                        <Sparkles class="size-3.5 text-gold" />
                        {{ today }}
                    </span>
                    <h1
                        class="mt-4 font-display text-3xl leading-tight font-bold md:text-4xl"
                    >
                        مرحبًا، {{ firstName }}
                    </h1>
                    <p
                        class="mt-2 max-w-md text-sm leading-relaxed text-white/75"
                    >
                        هذا ملخص نشاط المنصة اليوم: ما ينتظر قرارك أولًا، ثم
                        الصورة الكاملة.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <Link
                        :href="payments({ query: { status: 'under_review' } })"
                        class="group relative overflow-hidden rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur-md transition hover:-translate-y-0.5 hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        data-test="payments-under-review"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="relative flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-300 to-amber-500 text-amber-950 shadow-lg shadow-amber-500/30"
                            >
                                <Banknote class="size-5" />
                                <template v-if="underReview > 0">
                                    <span
                                        class="absolute -end-1 -top-1 size-3 animate-ping rounded-full bg-amber-300"
                                    ></span>
                                    <span
                                        class="absolute -end-1 -top-1 size-3 rounded-full bg-amber-300 ring-2 ring-white/40"
                                    ></span>
                                </template>
                            </span>
                            <CircleCheck
                                v-if="underReview === 0"
                                class="size-5 text-emerald-300"
                            />
                        </div>
                        <p class="mt-4 font-display text-4xl font-bold">
                            <AnimatedNumber :value="underReview" />
                        </p>
                        <p class="mt-1 text-sm text-white/75">
                            إيصالات بانتظار المراجعة
                        </p>
                        <p
                            class="mt-3 flex items-center gap-1 text-xs font-medium text-amber-200"
                        >
                            {{
                                underReview > 0
                                    ? 'مراجعة الآن'
                                    : 'لا شيء بانتظارك'
                            }}
                            <ArrowLeft
                                class="size-3.5 transition-transform group-hover:-translate-x-1"
                            />
                        </p>
                    </Link>

                    <Link
                        :href="studies({ query: { status: 'draft' } })"
                        class="group relative overflow-hidden rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur-md transition hover:-translate-y-0.5 hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        data-test="expiring"
                    >
                        <span
                            class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-300 to-sky-500 text-sky-950 shadow-lg shadow-sky-500/30"
                        >
                            <CalendarClock class="size-5" />
                        </span>
                        <p class="mt-4 font-display text-4xl font-bold">
                            <AnimatedNumber :value="studyCounts.expiring" />
                        </p>
                        <p class="mt-1 text-sm text-white/75">
                            مسودات تنتهي خلال
                            {{ formatNumber(expiringWithinDays) }} أيام
                        </p>
                        <p
                            class="mt-3 flex items-center gap-1 text-xs font-medium text-sky-200"
                        >
                            {{ formatNumber(studyCounts.expired) }} منتهية قد
                            تحتاج إلى تمديد
                            <ArrowLeft
                                class="size-3.5 transition-transform group-hover:-translate-x-1"
                            />
                        </p>
                    </Link>
                </div>
            </div>
        </Reveal>

        <!-- Key figures -->
        <section
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="أرقام رئيسية"
        >
            <Reveal
                v-for="(stat, index) in stats"
                :key="stat.label"
                :delay="80 * (index + 1)"
            >
                <Link
                    :href="stat.href"
                    class="group relative block h-full overflow-hidden rounded-2xl border bg-card p-5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/5 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none dark:hover:shadow-black/30"
                    :data-test="stat.test"
                >
                    <div
                        class="pointer-events-none absolute -end-12 -top-12 size-36 rounded-full opacity-60 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
                        :class="stat.glow"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="relative flex items-start justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <p class="text-sm text-muted-foreground">
                                {{ stat.label }}
                            </p>
                            <p
                                class="mt-3 flex items-baseline gap-1.5 font-display text-3xl font-bold"
                            >
                                <AnimatedNumber :value="stat.value" />
                                <span
                                    v-if="stat.money"
                                    class="text-base font-semibold text-muted-foreground"
                                    >{{ currencySymbol }}</span
                                >
                            </p>
                        </div>
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg ring-1 ring-white/20"
                            :class="stat.chip"
                        >
                            <component :is="stat.icon" class="size-5" />
                        </span>
                    </div>
                    <p
                        class="relative mt-3 border-t border-dashed pt-3 text-xs text-muted-foreground"
                    >
                        {{ stat.caption }}
                    </p>
                </Link>
            </Reveal>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_400px]">
            <Reveal :delay="120" class="min-w-0 space-y-6">
                <!-- Status breakdowns -->
                <section class="grid gap-6 md:grid-cols-2">
                    <div
                        v-for="group in breakdowns"
                        :key="group.title"
                        class="rounded-2xl border bg-card p-5"
                    >
                        <div class="flex items-baseline justify-between gap-2">
                            <h2 class="font-semibold">{{ group.title }}</h2>
                            <Link
                                :href="group.href"
                                class="flex items-center gap-1 text-xs text-muted-foreground transition-colors hover:text-primary"
                            >
                                عرض الكل
                                <ArrowLeft class="size-3" />
                            </Link>
                        </div>
                        <div class="mt-5 flex items-center gap-5">
                            <div class="relative size-28 shrink-0">
                                <DonutChart
                                    v-if="group.total > 0"
                                    :segments="
                                        group.segments.filter(
                                            (segment) => segment.value > 0,
                                        )
                                    "
                                    :thickness="12"
                                />
                                <div
                                    v-else
                                    class="size-full rounded-full border-[12px] border-muted"
                                ></div>
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center"
                                >
                                    <span
                                        class="font-display text-2xl font-bold"
                                    >
                                        <AnimatedNumber :value="group.total" />
                                    </span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ group.unit }}
                                    </span>
                                </div>
                            </div>
                            <ul class="min-w-0 flex-1 space-y-2.5 text-sm">
                                <li
                                    v-for="segment in group.segments"
                                    :key="segment.label"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="size-2.5 shrink-0 rounded-full"
                                        :style="{ background: segment.color }"
                                    ></span>
                                    <span
                                        class="min-w-0 flex-1 leading-tight text-muted-foreground"
                                        >{{ segment.label }}</span
                                    >
                                    <span class="font-semibold tabular-nums">{{
                                        formatNumber(segment.value)
                                    }}</span>
                                    <span
                                        class="w-10 text-end text-xs text-muted-foreground tabular-nums"
                                        >{{
                                            formatNumber(
                                                share(
                                                    segment.value,
                                                    group.total,
                                                ),
                                            )
                                        }}%</span
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Funnel -->
                <section class="rounded-2xl border bg-card p-5">
                    <h2 class="font-semibold">مسار الدراسات</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        من إنشاء الدراسة حتى إصدار تقريرها النهائي.
                    </p>
                    <ol class="mt-5 space-y-4" data-test="study-funnel">
                        <li
                            v-for="(step, index) in funnel"
                            :key="step.label"
                            class="grid grid-cols-[1.75rem_7rem_minmax(0,1fr)_5rem] items-center gap-3 text-sm"
                        >
                            <span
                                class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary"
                                >{{ index + 1 }}</span
                            >
                            <span class="text-muted-foreground">{{
                                step.label
                            }}</span>
                            <span
                                class="relative h-3 overflow-hidden rounded-full bg-muted"
                            >
                                <span
                                    class="absolute inset-y-0 start-0 rounded-full bg-gradient-to-l from-teal-400 to-primary transition-[width] duration-1000 ease-out"
                                    :style="{ width: `${step.share}%` }"
                                ></span>
                            </span>
                            <span
                                class="flex items-baseline justify-end gap-1.5"
                            >
                                <span class="font-semibold tabular-nums">{{
                                    formatNumber(step.value)
                                }}</span>
                                <span
                                    class="text-xs text-muted-foreground tabular-nums"
                                    >{{ formatNumber(step.share) }}%</span
                                >
                            </span>
                        </li>
                    </ol>
                </section>
            </Reveal>

            <!-- Recent activity -->
            <Reveal :delay="200" class="min-w-0">
                <section class="h-full rounded-2xl border bg-card p-5">
                    <div class="mb-5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="relative flex size-2">
                                <span
                                    class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"
                                ></span>
                                <span
                                    class="relative inline-flex size-2 rounded-full bg-emerald-500"
                                ></span>
                            </span>
                            <h2 class="font-semibold">آخر العمليات</h2>
                        </div>
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
            </Reveal>
        </div>
    </div>
</template>
