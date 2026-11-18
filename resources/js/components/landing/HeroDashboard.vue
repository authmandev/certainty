<script setup lang="ts">
import { FileText, Sparkles, TrendingUp } from '@lucide/vue';
import AnimatedNumber from '@/components/landing/AnimatedNumber.vue';
import DonutChart from '@/components/landing/DonutChart.vue';
import {
    sampleCapital,
    sampleCosts,
    sampleCurrency,
    sampleIndicators,
    sampleProfit,
    sampleRevenue,
    sampleYears,
} from '@/components/landing/sampleData';
import TrendChart from '@/components/landing/TrendChart.vue';
import { useParallax } from '@/composables/useParallax';

const slowLayer = useParallax(-0.06);
const fastLayer = useParallax(-0.12);

const chartSeries = [
    {
        label: 'الإيرادات',
        values: sampleRevenue,
        color: 'var(--chart-rev)',
        fill: true,
    },
    { label: 'التكاليف', values: sampleCosts, color: 'var(--chart-cost)' },
    {
        label: 'صافي الربح',
        values: sampleProfit,
        color: 'var(--gold)',
        fill: true,
    },
];

const capitalSegments = [
    {
        label: 'تمويل ذاتي',
        value: sampleCapital.selfFinanced,
        color: 'var(--chart-rev)',
    },
    { label: 'قروض', value: sampleCapital.loan, color: 'var(--gold)' },
];
</script>

<template>
    <div
        class="relative mx-auto w-full max-w-[640px] [--chart-cost:oklch(0.65_0.03_250)] [--chart-rev:var(--primary)]"
    >
        <!-- Main product window -->
        <div
            class="relative z-10 overflow-hidden rounded-2xl border border-white/60 bg-card/90 shadow-[0_30px_80px_-20px_rgb(15_40_60/0.35)] ring-1 ring-black/5 backdrop-blur-xl dark:border-white/10 dark:ring-white/5"
        >
            <div
                class="flex items-center justify-between border-b bg-muted/40 px-4 py-3"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-7 items-center justify-center rounded-lg bg-primary/15 text-primary"
                    >
                        <TrendingUp class="size-4" />
                    </span>
                    <div class="leading-tight">
                        <p class="text-sm font-semibold">ملخص الجدوى المالية</p>
                        <p class="text-[11px] text-muted-foreground">
                            مقهى مختص · 5 سنوات
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-full border border-dashed px-2.5 py-1 text-[11px] text-muted-foreground"
                    >مثال توضيحي</span
                >
            </div>

            <div class="space-y-5 p-4 sm:p-5">
                <dl class="grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="rounded-xl bg-muted/50 p-3">
                        <dt class="text-[11px] text-muted-foreground">
                            إيرادات السنة 5
                        </dt>
                        <dd
                            class="mt-1 font-display text-base font-bold sm:text-lg"
                        >
                            <AnimatedNumber :value="sampleRevenue[4]" />
                        </dd>
                    </div>
                    <div class="rounded-xl bg-muted/50 p-3">
                        <dt class="text-[11px] text-muted-foreground">
                            صافي ربح السنة 5
                        </dt>
                        <dd
                            class="mt-1 font-display text-base font-bold text-emerald-600 sm:text-lg dark:text-emerald-400"
                        >
                            <AnimatedNumber :value="sampleProfit[4]" />
                        </dd>
                    </div>
                    <div class="rounded-xl bg-muted/50 p-3">
                        <dt class="text-[11px] text-muted-foreground">
                            رأس المال
                        </dt>
                        <dd
                            class="mt-1 font-display text-base font-bold sm:text-lg"
                        >
                            <AnimatedNumber :value="sampleCapital.total" />
                        </dd>
                    </div>
                </dl>

                <div>
                    <div
                        class="mb-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-muted-foreground"
                    >
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
                        <span class="ms-auto">بالألف {{ sampleCurrency }}</span>
                    </div>
                    <TrendChart
                        :series="chartSeries"
                        :labels="sampleYears.map((year) => `س${year}`)"
                    />
                </div>
            </div>
        </div>

        <!-- Floating: payback period -->
        <div
            class="absolute -start-4 -top-6 z-20 hidden sm:block lg:-start-12"
            :style="fastLayer"
        >
            <div
                class="animate-float rounded-2xl border bg-card/95 p-4 shadow-xl backdrop-blur"
            >
                <p class="text-[11px] text-muted-foreground">
                    استرداد رأس المال
                </p>
                <p class="mt-1 font-display text-lg font-bold">
                    خلال {{ sampleIndicators.paybackYears }} سنوات
                </p>
                <div class="mt-2 flex gap-1">
                    <span
                        v-for="year in 5"
                        :key="year"
                        class="h-1.5 w-6 rounded-full"
                        :class="
                            year <= sampleIndicators.paybackYears
                                ? 'bg-primary'
                                : 'bg-muted'
                        "
                    ></span>
                </div>
            </div>
        </div>

        <!-- Floating: capital distribution -->
        <div
            class="absolute -end-3 -bottom-10 z-20 hidden sm:block lg:-end-10"
            :style="slowLayer"
        >
            <div
                class="flex animate-float-slow items-center gap-3 rounded-2xl border bg-card/95 p-4 shadow-xl backdrop-blur [--chart-rev:var(--primary)] [animation-delay:-3s]"
            >
                <div class="size-16">
                    <DonutChart :segments="capitalSegments" />
                </div>
                <div class="space-y-1.5 text-xs">
                    <p class="font-semibold">توزيع رأس المال</p>
                    <p
                        v-for="segment in capitalSegments"
                        :key="segment.label"
                        class="flex items-center gap-1.5 text-muted-foreground"
                    >
                        <span
                            class="size-2 rounded-full"
                            :style="{ background: segment.color }"
                        ></span>
                        {{ segment.label }}
                        {{
                            Math.round(
                                (segment.value / sampleCapital.total) * 100,
                            )
                        }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Floating: report ready -->
        <div
            class="absolute -end-6 top-1/3 z-20 hidden lg:block xl:-end-16"
            :style="fastLayer"
        >
            <div
                class="flex animate-float items-center gap-2 rounded-full border bg-card/95 py-2 ps-2 pe-4 shadow-lg backdrop-blur [animation-delay:-1.5s]"
            >
                <span
                    class="flex size-8 animate-pulse-ring items-center justify-center rounded-full bg-primary text-primary-foreground"
                >
                    <FileText class="size-4" />
                </span>
                <span class="text-xs font-medium">تقرير PDF جاهز</span>
                <Sparkles class="size-3.5 text-gold" />
            </div>
        </div>
    </div>
</template>
