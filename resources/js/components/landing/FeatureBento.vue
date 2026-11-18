<script setup lang="ts">
import {
    BarChart3,
    CalendarClock,
    FileText,
    Landmark,
    LineChart,
    Target,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import BarChart from '@/components/landing/BarChart.vue';
import Reveal from '@/components/landing/Reveal.vue';
import {
    sampleCosts,
    sampleRevenue,
    sampleYears,
} from '@/components/landing/sampleData';
import SectionHeading from '@/components/landing/SectionHeading.vue';
import { formatNumber } from '@/lib/format';

const { draftDurationDays } = defineProps<{
    draftDurationDays: number;
}>();

/**
 * A toy what-if model for the live-summary card. It is illustrative only and is not the platform's calculation engine.
 */
const unitPrice = ref(18);
const monthlyUnits = 3000;
const unitCost = 7;
const fixedMonthlyCosts = 26_000;

const monthlyProfit = computed(
    () => (unitPrice.value - unitCost) * monthlyUnits - fixedMonthlyCosts,
);
const monthlyRevenue = computed(() => unitPrice.value * monthlyUnits);
const profitShare = computed(() =>
    Math.max(
        Math.min((monthlyProfit.value / monthlyRevenue.value) * 100, 100),
        0,
    ),
);

const yearGroups = sampleYears.map((year, index) => ({
    label: `س${year}`,
    values: [sampleRevenue[index], sampleCosts[index]],
}));

const statements = ['قائمة الدخل', 'التدفقات النقدية', 'الميزانية'];

const formSteps = ['المشروع', 'السوق', 'التكاليف', 'المبيعات', 'SWOT'];
</script>

<template>
    <section id="features" class="relative scroll-mt-20 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <SectionHeading
                eyebrow="المزايا"
                title="كل ما تحتاجه لدراسة جدوى مالية متكاملة"
                description="محرك حسابات مالية مجرّب يحوّل بيانات مشروعك إلى قوائم مالية ومؤشرات ربحية واضحة، داخل واجهة عربية مصممة للبساطة."
            />

            <div class="mt-16 grid gap-5 lg:grid-cols-6">
                <!-- Live summary (interactive) -->
                <Reveal
                    class="group relative overflow-hidden rounded-3xl border bg-card p-7 transition-shadow hover:shadow-xl hover:shadow-primary/5 lg:col-span-4"
                >
                    <div
                        class="absolute -end-24 -top-24 size-64 rounded-full bg-primary/10 blur-3xl transition-opacity group-hover:opacity-80"
                        aria-hidden="true"
                    ></div>
                    <div class="relative grid gap-8 md:grid-cols-2">
                        <div>
                            <span
                                class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary"
                            >
                                <BarChart3 class="size-5" />
                            </span>
                            <h3 class="mt-5 font-display text-xl font-bold">
                                ملخص مالي يتحدث مع كل إدخال
                            </h3>
                            <p
                                class="mt-3 leading-relaxed text-muted-foreground"
                            >
                                غيّر سعرًا أو كمية أو تكلفة، وشاهد أثرها على
                                الإيرادات والأرباح فورًا قبل أن تعتمد دراستك.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border bg-background/70 p-5 backdrop-blur"
                        >
                            <div
                                class="flex items-center justify-between text-xs text-muted-foreground"
                            >
                                <label for="feature-unit-price"
                                    >جرّب: سعر بيع المنتج</label
                                >
                                <span
                                    class="rounded-full border border-dashed px-2 py-0.5"
                                    >مثال توضيحي</span
                                >
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <input
                                    id="feature-unit-price"
                                    v-model.number="unitPrice"
                                    type="range"
                                    min="10"
                                    max="25"
                                    step="1"
                                    class="h-2 w-full cursor-pointer accent-[var(--primary)]"
                                />
                                <span
                                    class="w-12 shrink-0 text-end font-display font-bold tabular-nums"
                                    >{{ unitPrice }}</span
                                >
                            </div>
                            <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-muted/60 p-3">
                                    <dt class="text-xs text-muted-foreground">
                                        الإيراد الشهري
                                    </dt>
                                    <dd
                                        class="mt-1 font-display text-lg font-bold tabular-nums"
                                    >
                                        {{ formatNumber(monthlyRevenue) }}
                                    </dd>
                                </div>
                                <div class="rounded-xl bg-muted/60 p-3">
                                    <dt class="text-xs text-muted-foreground">
                                        الربح الشهري
                                    </dt>
                                    <dd
                                        class="mt-1 font-display text-lg font-bold tabular-nums transition-colors"
                                        :class="
                                            monthlyProfit >= 0
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-red-600 dark:text-red-400'
                                        "
                                    >
                                        {{ formatNumber(monthlyProfit) }}
                                    </dd>
                                </div>
                            </dl>
                            <div
                                class="mt-4 h-2 overflow-hidden rounded-full bg-muted"
                                aria-hidden="true"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-l from-primary to-emerald-500 transition-[width] duration-500 ease-out"
                                    :style="{ width: `${profitShare}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </Reveal>

                <!-- Multi-year projections -->
                <Reveal
                    :delay="100"
                    class="group rounded-3xl border bg-card p-7 transition-shadow hover:shadow-xl hover:shadow-primary/5 lg:col-span-2"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <LineChart class="size-5" />
                    </span>
                    <h3 class="mt-5 font-display text-xl font-bold">
                        توقعات متعددة السنوات
                    </h3>
                    <p
                        class="mt-3 text-sm leading-relaxed text-muted-foreground"
                    >
                        اختر مدة الدراسة حتى 15 سنة مع نمو سنوي للمبيعات وحدود
                        للطاقة الإنتاجية.
                    </p>
                    <div class="mt-6 text-muted-foreground">
                        <BarChart
                            :groups="yearGroups"
                            :colors="['var(--primary)', 'oklch(0.75 0.03 250)']"
                            :height="140"
                        />
                    </div>
                </Reveal>

                <!-- Financial statements -->
                <Reveal
                    :delay="50"
                    class="group rounded-3xl border bg-card p-7 transition-shadow hover:shadow-xl hover:shadow-primary/5 lg:col-span-2"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <Landmark class="size-5" />
                    </span>
                    <h3 class="mt-5 font-display text-xl font-bold">
                        قوائم مالية لكل سنة
                    </h3>
                    <p
                        class="mt-3 text-sm leading-relaxed text-muted-foreground"
                    >
                        تُحسب القوائم المالية الثلاث تلقائيًا من بيانات مشروعك.
                    </p>
                    <div class="relative mt-6 h-32">
                        <div
                            v-for="(statement, index) in statements"
                            :key="statement"
                            class="absolute inset-x-0 rounded-xl border bg-background p-3 shadow-sm transition-transform duration-500 group-hover:translate-y-0"
                            :style="{
                                top: `${index * 22}px`,
                                transform: `translateY(${index * 4}px) scale(${1 - (2 - index) * 0.04})`,
                                zIndex: index,
                            }"
                        >
                            <p class="text-xs font-semibold">{{ statement }}</p>
                            <div class="mt-2 space-y-1.5" aria-hidden="true">
                                <div
                                    class="h-1.5 w-full rounded-full bg-muted"
                                ></div>
                                <div
                                    class="h-1.5 w-3/4 rounded-full bg-muted"
                                ></div>
                            </div>
                        </div>
                    </div>
                </Reveal>

                <!-- Cost-based pricing -->
                <Reveal
                    :delay="100"
                    class="group rounded-3xl border bg-card p-7 transition-shadow hover:shadow-xl hover:shadow-primary/5 lg:col-span-2"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <Target class="size-5" />
                    </span>
                    <h3 class="mt-5 font-display text-xl font-bold">
                        تسعير مبني على التكلفة
                    </h3>
                    <p
                        class="mt-3 text-sm leading-relaxed text-muted-foreground"
                    >
                        سعر التعادل لكل منتج وهامش الربح، مع فترة استرداد رأس
                        المال والحصة السوقية.
                    </p>
                    <div class="mt-6 space-y-3 text-xs">
                        <div>
                            <div
                                class="mb-1 flex justify-between text-muted-foreground"
                            >
                                <span>سعر التعادل</span><span>11.40</span>
                            </div>
                            <div class="h-2 rounded-full bg-muted">
                                <div
                                    class="h-full w-[57%] rounded-full bg-gold transition-[width] duration-700 group-hover:w-[62%]"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div
                                class="mb-1 flex justify-between text-muted-foreground"
                            >
                                <span>سعر البيع</span><span>18.00</span>
                            </div>
                            <div class="h-2 rounded-full bg-muted">
                                <div
                                    class="h-full w-[90%] rounded-full bg-primary"
                                ></div>
                            </div>
                        </div>
                    </div>
                </Reveal>

                <!-- Save and resume -->
                <Reveal
                    :delay="150"
                    class="group rounded-3xl border bg-card p-7 transition-shadow hover:shadow-xl hover:shadow-primary/5 lg:col-span-2"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <CalendarClock class="size-5" />
                    </span>
                    <h3 class="mt-5 font-display text-xl font-bold">
                        احفظ وأكمل لاحقًا
                    </h3>
                    <p
                        class="mt-3 text-sm leading-relaxed text-muted-foreground"
                    >
                        لديك {{ draftDurationDays }} يومًا لإكمال دراستك على
                        مراحل، وتقدّمك محفوظ دائمًا.
                    </p>
                    <ol class="mt-6 flex gap-1.5">
                        <li
                            v-for="(step, index) in formSteps"
                            :key="step"
                            class="flex-1"
                        >
                            <div
                                class="h-1.5 rounded-full transition-colors duration-500"
                                :class="
                                    index < 3
                                        ? 'bg-primary'
                                        : 'bg-muted group-hover:bg-primary/40'
                                "
                            ></div>
                            <p
                                class="mt-2 truncate text-center text-[11px] text-muted-foreground"
                            >
                                {{ step }}
                            </p>
                        </li>
                    </ol>
                </Reveal>

                <!-- PDF report -->
                <Reveal
                    :delay="50"
                    class="group relative overflow-hidden rounded-3xl border bg-gradient-to-br from-ink to-ink/90 p-7 text-white lg:col-span-6"
                >
                    <div
                        class="bg-grid-pattern absolute inset-0 opacity-30 [--foreground:white]"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="relative flex flex-col items-start gap-6 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="flex items-start gap-4">
                            <span
                                class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-gold"
                            >
                                <FileText class="size-6" />
                            </span>
                            <div>
                                <h3 class="font-display text-xl font-bold">
                                    تقرير PDF احترافي بهوية Certainty
                                </h3>
                                <p
                                    class="mt-2 max-w-2xl leading-relaxed text-white/70"
                                >
                                    بعد اعتماد الدفع وإنهاء الدراسة تحصل على
                                    تقرير منسّق يضم الأقسام الوصفية والقوائم
                                    المالية والرسوم البيانية، جاهز للطباعة
                                    والمشاركة.
                                </p>
                            </div>
                        </div>
                        <a
                            href="#report"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-white/10 px-5 py-2.5 text-sm font-medium ring-1 ring-white/20 transition-colors hover:bg-white/20"
                            >معاينة التقرير</a
                        >
                    </div>
                </Reveal>
            </div>
        </div>
    </section>
</template>
