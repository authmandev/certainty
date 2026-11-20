<script setup lang="ts">
import {
    ChartNoAxesCombined,
    CircleAlert,
    Info,
    LoaderCircle,
    TriangleAlert,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import DonutChart from '@/components/landing/DonutChart.vue';
import ProfitabilityChart from '@/components/study/results/ProfitabilityChart.vue';
import { formatCurrency, formatNumber, formatPercent } from '@/lib/format';
import type { PreviewWarning, SectionKey, StudyPreview } from '@/types';

const { preview, stale, updating } = defineProps<{
    preview: StudyPreview | null;
    stale: boolean;
    updating: boolean;
}>();

defineEmits<{
    goTo: [section: SectionKey];
}>();

const colors = {
    revenue: 'var(--primary)',
    expenses: 'oklch(0.65 0.03 250)',
    profit: 'var(--gold)',
};

function money(value: number): string {
    if (!preview?.currency) {
        return formatNumber(value, preview?.decimals ?? 0);
    }

    return formatCurrency(value, preview.currency, preview.decimals);
}

/**
 * Nothing to show until the customer enters costs or sales.
 */
const isEmpty = computed(
    () =>
        preview !== null &&
        preview.yearly.every(
            (year) => year.revenue === 0 && year.expenses === 0,
        ),
);

const keyFigures = computed(() => {
    if (!preview) {
        return [];
    }

    const { summary, indicators } = preview;

    return [
        { label: 'إجمالي رأس المال', value: money(summary.total_capital) },
        {
            label: 'القروض مع الفوائد',
            value: money(summary.total_liabilities_payable),
        },
        { label: 'التمويل الذاتي', value: money(summary.self_financing) },
        {
            label: 'الإيرادات الشهرية المتوقعة',
            hint: 'السنة الأولى، دون الضريبة',
            value: money(summary.monthly_revenue_without_vat),
        },
        { label: 'الرواتب الشهرية', value: money(summary.monthly_salaries) },
        {
            label: 'التكاليف الثابتة الشهرية',
            hint: 'تشمل أقساط القروض',
            value: money(summary.monthly_fixed_costs),
        },
        {
            label: `إجمالي المبيعات خلال ${formatNumber(preview.study_years)} ${preview.study_years > 2 && preview.study_years < 11 ? 'سنوات' : 'سنة'}`,
            value: money(indicators.total_sales),
        },
        ...(indicators.market_share !== null
            ? [
                  {
                      label: 'الحصة السوقية المتوقعة',
                      value: formatPercent(indicators.market_share, 2),
                  },
              ]
            : []),
    ];
});

const chartLabels = computed(
    () => preview?.yearly.map((year) => `س${formatNumber(year.year)}`) ?? [],
);

const chartSeries = computed(() => [
    {
        label: 'الإيرادات',
        values: preview?.yearly.map((year) => year.revenue) ?? [],
        color: colors.revenue,
    },
    {
        label: 'المصروفات',
        values: preview?.yearly.map((year) => year.expenses) ?? [],
        color: colors.expenses,
        dashed: true,
    },
    {
        label: 'صافي الربح',
        values: preview?.yearly.map((year) => year.profit) ?? [],
        color: colors.profit,
    },
]);

const hoveredYear = ref<number | null>(null);

const financing = computed(() => {
    if (!preview || preview.summary.total_capital <= 0) {
        return null;
    }

    const borrowed = Math.max(preview.summary.total_borrowed, 0);
    const own = Math.max(preview.summary.self_financing, 0);
    const total = borrowed + own;

    if (total <= 0) {
        return null;
    }

    return [
        {
            label: 'قروض',
            value: borrowed,
            color: colors.profit,
            share: (borrowed / total) * 100,
        },
        {
            label: 'تمويل ذاتي',
            value: own,
            color: colors.revenue,
            share: (own / total) * 100,
        },
    ].filter((segment) => segment.value > 0);
});

const setupCosts = computed(() => {
    if (!preview) {
        return [];
    }

    const total = preview.setup_costs.reduce(
        (sum, cost) => sum + Math.max(cost.value, 0),
        0,
    );

    return [...preview.setup_costs]
        .filter((cost) => cost.value > 0)
        .sort((a, b) => b.value - a.value)
        .map((cost) => ({
            ...cost,
            share: total > 0 ? (cost.value / total) * 100 : 0,
        }));
});

const sectionTitles: Record<PreviewWarning['section'], string> = {
    basic: 'المعلومات الأساسية',
    market: 'السوق',
    costs: 'التكاليف',
    sales: 'المبيعات',
    swot: 'التحليل الرباعي',
    results: 'النتائج',
};
</script>

<template>
    <section
        class="rounded-2xl border bg-card"
        aria-labelledby="results-panel-title"
    >
        <header class="flex items-center justify-between gap-3 border-b p-4">
            <div class="flex items-center gap-2.5">
                <span
                    class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <ChartNoAxesCombined class="size-4" />
                </span>
                <h2 id="results-panel-title" class="font-display font-bold">
                    النتائج المباشرة
                </h2>
            </div>
            <span
                class="flex items-center gap-1.5 text-xs"
                role="status"
                aria-live="polite"
                data-test="preview-status"
            >
                <template v-if="updating">
                    <LoaderCircle
                        class="size-3.5 animate-spin text-muted-foreground"
                    />
                    <span class="text-muted-foreground">جارٍ التحديث…</span>
                </template>
                <template v-else-if="stale">
                    <span class="size-2 rounded-full bg-amber-500"></span>
                    <span class="text-muted-foreground"
                        >بانتظار حفظ التعديلات</span
                    >
                </template>
                <template v-else>
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    <span class="text-muted-foreground">محدّثة</span>
                </template>
            </span>
        </header>

        <div
            v-if="preview === null"
            class="flex flex-col items-center gap-2 p-8 text-center text-sm"
        >
            <CircleAlert class="size-6 text-red-600 dark:text-red-400" />
            <p class="font-medium">تعذّر حساب النتائج بالقيم الحالية</p>
            <p class="text-muted-foreground">
                راجع القيم الكبيرة جدًا أو غير المنطقية، وستظهر النتائج بعد
                تصحيحها.
            </p>
        </div>

        <div
            v-else-if="isEmpty"
            class="flex flex-col items-center gap-2 p-8 text-center text-sm"
            data-test="preview-empty"
        >
            <ChartNoAxesCombined class="size-7 text-muted-foreground/60" />
            <p class="font-medium">لا توجد نتائج بعد</p>
            <p class="text-muted-foreground">
                أدخل تكاليف التأسيس والمنتجات وأسعارها، وستظهر الأرقام هنا
                مباشرةً أثناء الكتابة.
            </p>
        </div>

        <div
            v-else
            class="space-y-6 p-4 transition-opacity duration-300"
            :class="stale || updating ? 'opacity-70' : 'opacity-100'"
        >
            <!-- Headline indicators -->
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-muted/60 p-3">
                    <p class="text-xs text-muted-foreground">
                        متوسط صافي الربح السنوي
                    </p>
                    <p
                        class="mt-1 font-display text-base leading-tight font-bold whitespace-nowrap tabular-nums"
                        :class="
                            preview.indicators.average_profit < 0
                                ? 'text-red-600 dark:text-red-400'
                                : ''
                        "
                        data-test="preview-average-profit"
                    >
                        {{ money(preview.indicators.average_profit) }}
                    </p>
                </div>
                <div class="rounded-xl bg-muted/60 p-3">
                    <p class="text-xs text-muted-foreground">
                        استرداد رأس المال
                    </p>
                    <p
                        class="mt-1 font-display text-lg leading-tight font-bold"
                    >
                        {{ preview.indicators.capital_recovery.label }}
                    </p>
                </div>
            </div>

            <dl class="divide-y text-sm">
                <div
                    v-for="figure in keyFigures"
                    :key="figure.label"
                    class="flex items-baseline justify-between gap-3 py-2"
                >
                    <dt class="text-muted-foreground">
                        {{ figure.label }}
                        <span
                            v-if="figure.hint"
                            class="block text-xs text-muted-foreground/70"
                            >{{ figure.hint }}</span
                        >
                    </dt>
                    <dd class="text-end font-medium tabular-nums">
                        {{ figure.value }}
                    </dd>
                </div>
            </dl>

            <!-- Revenue, expenses and profit by year -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold">الربحية حسب السنة</h3>
                    <div class="flex gap-3 text-xs text-muted-foreground">
                        <span
                            v-for="item in chartSeries"
                            :key="item.label"
                            class="flex items-center gap-1.5"
                        >
                            <span
                                class="size-2 rounded-full"
                                :style="{ background: item.color }"
                            ></span>
                            {{ item.label }}
                        </span>
                    </div>
                </div>
                <div class="mt-3 text-foreground">
                    <ProfitabilityChart
                        v-model:hovered="hoveredYear"
                        :series="chartSeries"
                        :labels="chartLabels"
                    />
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-xs tabular-nums">
                        <thead class="text-muted-foreground">
                            <tr class="border-b">
                                <th class="py-1.5 text-start font-medium">
                                    السنة
                                </th>
                                <th class="py-1.5 text-end font-medium">
                                    الإيرادات
                                </th>
                                <th class="py-1.5 text-end font-medium">
                                    المصروفات
                                </th>
                                <th class="py-1.5 text-end font-medium">
                                    صافي الربح
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(year, index) in preview.yearly"
                                :key="year.year"
                                class="border-b transition-colors last:border-0"
                                :class="hoveredYear === index ? 'bg-muted' : ''"
                                @mouseenter="hoveredYear = index"
                                @mouseleave="hoveredYear = null"
                            >
                                <td class="py-1.5">
                                    {{ formatNumber(year.year) }}
                                </td>
                                <td class="py-1.5 text-end">
                                    {{ formatNumber(year.revenue) }}
                                </td>
                                <td class="py-1.5 text-end">
                                    {{ formatNumber(year.expenses) }}
                                </td>
                                <td
                                    class="py-1.5 text-end font-medium"
                                    :class="
                                        year.profit < 0
                                            ? 'text-red-600 dark:text-red-400'
                                            : ''
                                    "
                                >
                                    {{ formatNumber(year.profit) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Capital distribution -->
            <div v-if="financing && financing.length > 0">
                <h3 class="text-sm font-semibold">توزيع رأس المال</h3>
                <div class="mt-3 flex items-center gap-4">
                    <div class="size-24 shrink-0">
                        <DonutChart :segments="financing" :thickness="16" />
                    </div>
                    <ul class="min-w-0 flex-1 space-y-2 text-sm">
                        <li
                            v-for="segment in financing"
                            :key="segment.label"
                            class="flex items-center justify-between gap-2"
                        >
                            <span class="flex items-center gap-1.5">
                                <span
                                    class="size-2 rounded-full"
                                    :style="{ background: segment.color }"
                                ></span>
                                {{ segment.label }}
                            </span>
                            <span class="tabular-nums">
                                {{ formatPercent(segment.share, 0) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Setup costs -->
            <div v-if="setupCosts.length > 0">
                <h3 class="text-sm font-semibold">تكاليف التأسيس</h3>
                <ul class="mt-3 space-y-2.5">
                    <li v-for="cost in setupCosts" :key="cost.name">
                        <div
                            class="flex items-baseline justify-between gap-2 text-xs"
                        >
                            <span class="min-w-0 truncate">
                                {{ cost.name }}
                                <span
                                    v-if="cost.is_study_fee"
                                    class="text-muted-foreground"
                                    >(تُضاف تلقائيًا)</span
                                >
                            </span>
                            <span
                                class="shrink-0 text-muted-foreground tabular-nums"
                            >
                                {{ money(cost.value) }}
                            </span>
                        </div>
                        <div
                            class="mt-1 h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-[width] duration-500 ease-out"
                                :class="
                                    cost.is_study_fee ||
                                    cost.is_unused_borrowed_funds
                                        ? 'bg-muted-foreground/40'
                                        : 'bg-primary'
                                "
                                :style="{ width: `${cost.share}%` }"
                            ></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Warnings -->
        <div
            v-if="preview && preview.warnings.length > 0"
            class="border-t p-4"
            data-test="preview-warnings"
        >
            <h3 class="flex items-center gap-1.5 text-sm font-semibold">
                <TriangleAlert
                    class="size-4 text-amber-600 dark:text-amber-400"
                />
                تنبيهات ({{ formatNumber(preview.warnings.length) }})
            </h3>
            <ul class="mt-3 space-y-2 text-sm">
                <li
                    v-for="(warning, index) in preview.warnings"
                    :key="`${warning.code}-${index}`"
                    class="rounded-lg border border-amber-500/25 bg-amber-500/5 p-2.5"
                >
                    <p>{{ warning.message }}</p>
                    <button
                        v-if="warning.section !== 'results'"
                        type="button"
                        class="mt-1 text-xs font-medium text-primary underline-offset-4 hover:underline"
                        @click="$emit('goTo', warning.section)"
                    >
                        الانتقال إلى {{ sectionTitles[warning.section] }}
                    </button>
                </li>
            </ul>
        </div>

        <p
            class="flex items-start gap-1.5 border-t p-4 text-xs text-muted-foreground"
        >
            <Info class="mt-0.5 size-3.5 shrink-0" />
            أرقام تقديرية تُحسب من البيانات المحفوظة بنفس معادلات التقرير، وتصبح
            نهائية عند إنهاء الدراسة.
        </p>
    </section>
</template>
