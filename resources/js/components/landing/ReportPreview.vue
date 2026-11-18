<script setup lang="ts">
import { Check } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Reveal from '@/components/landing/Reveal.vue';
import { sampleStatements } from '@/components/landing/sampleData';
import SectionHeading from '@/components/landing/SectionHeading.vue';
import TrendChart from '@/components/landing/TrendChart.vue';
import { formatNumber } from '@/lib/format';

const reportContents = [
    'نبذة المشروع والملاك والجدوى التنظيمية',
    'دراسة السوق والمنافسين',
    'تكاليف التأسيس والقروض والرواتب والتكاليف الثابتة',
    'المنتجات والمبيعات المتوقعة',
    'قائمة الدخل والتدفقات النقدية والميزانية لكل سنة',
    'الربحية السنوية والتسعير بالتكلفة',
    'فترة استرداد رأس المال والحصة السوقية',
    'تحليل SWOT والرسوم البيانية',
];

const incomeRows = sampleStatements.income.rows;
</script>

<template>
    <section
        id="report"
        class="relative scroll-mt-20 overflow-hidden border-y bg-muted/30 py-24 sm:py-32"
    >
        <div
            class="mx-auto grid max-w-7xl items-center gap-16 px-4 sm:px-6 lg:grid-cols-2"
        >
            <div>
                <SectionHeading
                    align="start"
                    eyebrow="التقرير النهائي"
                    title="تقرير جدوى بمظهر احترافي، جاهز للممولين والشركاء"
                    description="يُبنى التقرير من بياناتك ونتائج الحسابات المعتمدة، ويُحفظ بنسخة ثابتة لا تتغير بعد إصداره."
                />
                <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                    <Reveal
                        v-for="(item, index) in reportContents"
                        :key="item"
                        as="li"
                        :delay="index * 60"
                        class="flex items-start gap-2.5 text-sm"
                    >
                        <span
                            class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary"
                        >
                            <Check class="size-3" />
                        </span>
                        {{ item }}
                    </Reveal>
                </ul>
            </div>

            <!-- Stacked report pages -->
            <Reveal
                class="group relative mx-auto h-[460px] w-full max-w-[420px] [perspective:1600px] sm:h-[520px]"
            >
                <!-- Back page: financial statement -->
                <div
                    class="absolute inset-0 origin-bottom-left rotate-[5deg] rounded-xl border bg-card p-6 shadow-lg transition-transform duration-700 ease-out group-hover:translate-x-[-8%] group-hover:rotate-[8deg]"
                    aria-hidden="true"
                >
                    <p class="text-xs font-semibold text-primary">
                        القوائم المالية
                    </p>
                    <p class="mt-1 text-sm font-bold">قائمة الدخل · السنة 1</p>
                    <table class="mt-4 w-full text-[11px]">
                        <tbody>
                            <tr
                                v-for="row in incomeRows"
                                :key="row.label"
                                class="border-t"
                                :class="row.emphasis ? 'font-bold' : ''"
                            >
                                <td class="py-2">{{ row.label }}</td>
                                <td class="py-2 text-end tabular-nums">
                                    {{ formatNumber(Math.abs(row.values[0])) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Middle page: chart -->
                <div
                    class="absolute inset-0 origin-bottom-right -rotate-[3deg] rounded-xl border bg-card p-6 shadow-xl transition-transform duration-700 ease-out group-hover:translate-x-[6%] group-hover:-rotate-[6deg]"
                    aria-hidden="true"
                >
                    <p class="text-xs font-semibold text-primary">
                        الربحية السنوية
                    </p>
                    <div class="mt-6 text-muted-foreground">
                        <TrendChart
                            :series="[
                                {
                                    label: 'الإيرادات',
                                    values: [420, 515, 600, 676, 748],
                                    color: 'var(--primary)',
                                    fill: true,
                                },
                                {
                                    label: 'الأرباح',
                                    values: [25, 77, 128, 168, 207],
                                    color: 'var(--gold)',
                                },
                            ]"
                            :labels="['س1', 'س2', 'س3', 'س4', 'س5']"
                            :height="200"
                        />
                    </div>
                </div>

                <!-- Front page: cover -->
                <div
                    class="absolute inset-0 flex flex-col overflow-hidden rounded-xl border bg-card shadow-2xl transition-transform duration-700 ease-out group-hover:-translate-y-2"
                >
                    <div
                        class="relative h-2/5 overflow-hidden bg-gradient-to-br from-ink via-ink to-primary p-6 text-white"
                    >
                        <div
                            class="bg-grid-pattern absolute inset-0 opacity-25 [--foreground:white]"
                            aria-hidden="true"
                        ></div>
                        <div class="relative flex items-center gap-2">
                            <AppLogoIcon
                                class="size-7 fill-current text-gold"
                            />
                            <span class="font-display font-bold"
                                >Certainty</span
                            >
                        </div>
                        <p class="relative mt-8 text-xs text-white/70">
                            دراسة الجدوى المالية
                        </p>
                        <p
                            class="relative mt-1 font-display text-2xl font-bold"
                        >
                            مقهى الصباح
                        </p>
                    </div>
                    <div
                        class="flex flex-1 flex-col justify-between p-6 text-sm"
                    >
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    رقم الدراسة
                                </dt>
                                <dd class="font-medium" dir="ltr">CS-000042</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    مدة الدراسة
                                </dt>
                                <dd class="font-medium">5 سنوات</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    نسخة التقرير
                                </dt>
                                <dd class="font-medium">1</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    العملة
                                </dt>
                                <dd class="font-medium">ريال سعودي</dd>
                            </div>
                        </dl>
                        <div class="space-y-2" aria-hidden="true">
                            <div class="h-1.5 rounded-full bg-muted"></div>
                            <div
                                class="h-1.5 w-5/6 rounded-full bg-muted"
                            ></div>
                            <div
                                class="h-1.5 w-2/3 rounded-full bg-muted"
                            ></div>
                        </div>
                        <p
                            class="self-start rounded-full border border-dashed px-2.5 py-0.5 text-[11px] text-muted-foreground"
                        >
                            مثال توضيحي
                        </p>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>
</template>
