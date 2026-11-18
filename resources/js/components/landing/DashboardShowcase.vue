<script setup lang="ts">
import { Check, Circle, TrendingUp } from '@lucide/vue';
import { computed, ref } from 'vue';
import AnimatedNumber from '@/components/landing/AnimatedNumber.vue';
import BarChart from '@/components/landing/BarChart.vue';
import Reveal from '@/components/landing/Reveal.vue';
import {
    sampleCosts,
    sampleIndicators,
    sampleRevenue,
    sampleStatements,
    sampleYears,
} from '@/components/landing/sampleData';
import SectionHeading from '@/components/landing/SectionHeading.vue';
import { formatNumber } from '@/lib/format';

type StatementKey = keyof typeof sampleStatements;

const statementTabs = Object.entries(sampleStatements).map(([key, value]) => ({
    key: key as StatementKey,
    title: value.title,
}));

const activeStatement = ref<StatementKey>('income');
const statement = computed(() => sampleStatements[activeStatement.value]);

const formSteps = [
    { title: 'معلومات أساسية', done: true },
    { title: 'دراسة السوق', done: true },
    { title: 'التكاليف', done: true },
    { title: 'المبيعات', done: false },
    { title: 'تحليل SWOT', done: false },
];

const yearGroups = sampleYears.map((year, index) => ({
    label: `السنة ${year}`,
    values: [sampleRevenue[index], sampleCosts[index]],
}));

function formatCell(value: number): string {
    return value < 0
        ? `(${formatNumber(Math.abs(value))})`
        : formatNumber(value);
}
</script>

<template>
    <section id="preview" class="relative scroll-mt-20 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <SectionHeading
                eyebrow="معاينة المنصة"
                title="نتائج مالية واضحة أثناء إعداد دراستك"
                description="تابع تقدّمك في مراحل الدراسة، وراجع الإيرادات والتكاليف والقوائم المالية لكل سنة في واجهة واحدة."
            />

            <Reveal class="relative mt-16">
                <div
                    class="absolute -inset-x-6 -inset-y-10 -z-10 rounded-[3rem] bg-gradient-to-b from-primary/10 via-transparent to-gold/10 blur-2xl"
                    aria-hidden="true"
                ></div>

                <div
                    class="overflow-hidden rounded-3xl border bg-card shadow-[0_40px_100px_-30px_rgb(15_40_60/0.35)]"
                >
                    <!-- Window chrome -->
                    <div
                        class="flex items-center gap-3 border-b bg-muted/40 px-5 py-3"
                    >
                        <div class="flex gap-1.5" aria-hidden="true">
                            <span
                                class="size-2.5 rounded-full bg-red-400/70"
                            ></span>
                            <span
                                class="size-2.5 rounded-full bg-amber-400/70"
                            ></span>
                            <span
                                class="size-2.5 rounded-full bg-emerald-400/70"
                            ></span>
                        </div>
                        <p class="truncate text-sm font-medium">
                            دراسة جدوى مقهى مختص
                        </p>
                        <span
                            class="ms-auto shrink-0 rounded-full border border-dashed px-2.5 py-0.5 text-[11px] text-muted-foreground"
                            >مثال توضيحي</span
                        >
                    </div>

                    <div class="grid lg:grid-cols-[220px_1fr]">
                        <!-- Study steps -->
                        <aside class="hidden border-e bg-muted/20 p-5 lg:block">
                            <p
                                class="text-xs font-semibold text-muted-foreground"
                            >
                                مراحل الدراسة
                            </p>
                            <ol class="mt-4 space-y-1">
                                <li
                                    v-for="(step, index) in formSteps"
                                    :key="step.title"
                                    class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm"
                                    :class="
                                        index === 3
                                            ? 'bg-primary/10 font-medium text-primary'
                                            : ''
                                    "
                                >
                                    <span
                                        class="flex size-5 items-center justify-center rounded-full"
                                        :class="
                                            step.done
                                                ? 'bg-primary text-primary-foreground'
                                                : 'border'
                                        "
                                    >
                                        <Check
                                            v-if="step.done"
                                            class="size-3"
                                        />
                                        <Circle
                                            v-else
                                            class="size-2 fill-current opacity-40"
                                        />
                                    </span>
                                    {{ step.title }}
                                </li>
                            </ol>
                            <div
                                class="mt-6 rounded-xl border bg-background p-3"
                            >
                                <p class="text-xs text-muted-foreground">
                                    نسبة الإكمال
                                </p>
                                <p class="mt-1 font-display text-2xl font-bold">
                                    60%
                                </p>
                                <div class="mt-2 h-1.5 rounded-full bg-muted">
                                    <div
                                        class="h-full w-3/5 rounded-full bg-primary"
                                    ></div>
                                </div>
                            </div>
                        </aside>

                        <div class="min-w-0 space-y-6 p-5 sm:p-6">
                            <!-- KPIs -->
                            <dl class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                <div class="rounded-2xl border p-4">
                                    <dt class="text-xs text-muted-foreground">
                                        متوسط الربح السنوي
                                    </dt>
                                    <dd
                                        class="mt-1 font-display text-xl font-bold"
                                    >
                                        <AnimatedNumber
                                            :value="
                                                sampleIndicators.averageProfit
                                            "
                                        />
                                    </dd>
                                </div>
                                <div class="rounded-2xl border p-4">
                                    <dt class="text-xs text-muted-foreground">
                                        هامش ربح السنة 5
                                    </dt>
                                    <dd
                                        class="mt-1 flex items-center gap-1 font-display text-xl font-bold text-emerald-600 dark:text-emerald-400"
                                    >
                                        <AnimatedNumber
                                            :value="
                                                sampleIndicators.profitMargin
                                            "
                                            :decimals="1"
                                        />%
                                        <TrendingUp class="size-4" />
                                    </dd>
                                </div>
                                <div class="rounded-2xl border p-4">
                                    <dt class="text-xs text-muted-foreground">
                                        استرداد رأس المال
                                    </dt>
                                    <dd
                                        class="mt-1 font-display text-xl font-bold"
                                    >
                                        خلال
                                        {{ sampleIndicators.paybackYears }}
                                        سنوات
                                    </dd>
                                </div>
                                <div class="rounded-2xl border p-4">
                                    <dt class="text-xs text-muted-foreground">
                                        الحصة السوقية
                                    </dt>
                                    <dd
                                        class="mt-1 font-display text-xl font-bold"
                                    >
                                        <AnimatedNumber
                                            :value="
                                                sampleIndicators.marketShare
                                            "
                                            :decimals="1"
                                        />%
                                    </dd>
                                </div>
                            </dl>

                            <div class="grid gap-6 xl:grid-cols-5">
                                <!-- Revenue vs costs -->
                                <div
                                    class="rounded-2xl border p-5 xl:col-span-2"
                                >
                                    <p class="font-semibold">
                                        الإيرادات مقابل التكاليف
                                    </p>
                                    <div
                                        class="mt-1 flex gap-4 text-xs text-muted-foreground"
                                    >
                                        <span class="flex items-center gap-1.5">
                                            <span
                                                class="size-2 rounded-full bg-primary"
                                            ></span
                                            >إيرادات
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span
                                                class="size-2 rounded-full bg-[oklch(0.75_0.03_250)]"
                                            ></span
                                            >تكاليف
                                        </span>
                                    </div>
                                    <div class="mt-4 text-foreground">
                                        <BarChart
                                            :groups="yearGroups.slice(0, 5)"
                                            :colors="[
                                                'var(--primary)',
                                                'oklch(0.75 0.03 250)',
                                            ]"
                                            :height="190"
                                        />
                                    </div>
                                </div>

                                <!-- Statements -->
                                <div
                                    class="min-w-0 rounded-2xl border p-5 xl:col-span-3"
                                >
                                    <div
                                        class="flex flex-wrap gap-1 rounded-xl bg-muted p-1"
                                        role="tablist"
                                        aria-label="القوائم المالية"
                                    >
                                        <button
                                            v-for="tab in statementTabs"
                                            :key="tab.key"
                                            type="button"
                                            role="tab"
                                            :aria-selected="
                                                activeStatement === tab.key
                                            "
                                            class="flex-1 rounded-lg px-3 py-1.5 text-sm font-medium transition-all"
                                            :class="
                                                activeStatement === tab.key
                                                    ? 'bg-background text-foreground shadow-sm'
                                                    : 'text-muted-foreground hover:text-foreground'
                                            "
                                            @click="activeStatement = tab.key"
                                        >
                                            {{ tab.title }}
                                        </button>
                                    </div>

                                    <div
                                        class="mt-4 overflow-x-auto"
                                        role="tabpanel"
                                    >
                                        <Transition
                                            mode="out-in"
                                            enter-active-class="transition duration-300 ease-out"
                                            enter-from-class="opacity-0 translate-y-2"
                                            leave-active-class="transition duration-150"
                                            leave-to-class="opacity-0"
                                        >
                                            <table
                                                :key="activeStatement"
                                                class="w-full min-w-[420px] text-sm"
                                            >
                                                <thead>
                                                    <tr
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        <th
                                                            class="py-2 text-start font-medium"
                                                        >
                                                            البند
                                                        </th>
                                                        <th
                                                            v-for="year in 3"
                                                            :key="year"
                                                            class="py-2 text-end font-medium"
                                                        >
                                                            السنة {{ year }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="row in statement.rows"
                                                        :key="row.label"
                                                        class="border-t"
                                                        :class="
                                                            row.emphasis
                                                                ? 'font-bold'
                                                                : ''
                                                        "
                                                    >
                                                        <td class="py-2.5">
                                                            {{ row.label }}
                                                        </td>
                                                        <td
                                                            v-for="(
                                                                value, index
                                                            ) in row.values"
                                                            :key="index"
                                                            class="py-2.5 text-end tabular-nums"
                                                            :class="
                                                                row.emphasis
                                                                    ? 'text-primary'
                                                                    : value < 0
                                                                      ? 'text-muted-foreground'
                                                                      : ''
                                                            "
                                                        >
                                                            {{
                                                                formatCell(
                                                                    value,
                                                                )
                                                            }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </Transition>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>
</template>
