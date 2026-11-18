<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Banknote, Check, FileUp, ShieldCheck } from '@lucide/vue';
import Reveal from '@/components/landing/Reveal.vue';
import SectionHeading from '@/components/landing/SectionHeading.vue';
import { Button } from '@/components/ui/button';
import { create as createStudy } from '@/routes/studies';

const { formattedPrice, draftDurationDays } = defineProps<{
    formattedPrice: string;
    draftDurationDays: number;
}>();

const includes = [
    'دراسة جدوى مالية كاملة لمشروع واحد',
    'نموذج موجّه من خمس مراحل',
    'ملخص مالي ورسوم بيانية مباشرة أثناء الإدخال',
    'قائمة الدخل والتدفقات النقدية والميزانية لكل سنة',
    'تقرير PDF نهائي قابل للطباعة والمشاركة',
];

const paymentSteps = [
    {
        icon: Banknote,
        title: 'حوّل الرسوم',
        text: 'بالتحويل البنكي مع رقم المرجع',
    },
    {
        icon: FileUp,
        title: 'ارفع الإيصال',
        text: 'صورة أو PDF من صفحة الدراسة',
    },
    {
        icon: ShieldCheck,
        title: 'نعتمد الدفع',
        text: 'ثم أنهِ الدراسة وحمّل التقرير',
    },
];
</script>

<template>
    <section id="pricing" class="relative scroll-mt-20 py-24 sm:py-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <SectionHeading
                eyebrow="الأسعار"
                title="سعر واحد واضح لكل دراسة"
                description="بدون اشتراكات أو رسوم خفية. تدفع مرة واحدة لكل دراسة تحتاجها."
            />

            <Reveal class="relative mt-16">
                <div
                    class="absolute -inset-px rounded-[2rem] bg-gradient-to-l from-primary via-teal-400 to-gold opacity-70 blur-sm"
                    aria-hidden="true"
                ></div>
                <div
                    class="relative grid overflow-hidden rounded-[2rem] bg-card lg:grid-cols-5"
                >
                    <div class="p-8 sm:p-10 lg:col-span-3">
                        <p
                            class="inline-flex rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary"
                        >
                            دراسة جدوى واحدة
                        </p>
                        <p class="mt-6 flex flex-wrap items-baseline gap-x-3">
                            <span
                                class="font-display text-5xl font-bold tracking-tight tabular-nums sm:text-6xl"
                                data-test="study-price"
                                >{{ formattedPrice }}</span
                            >
                            <span class="text-muted-foreground"
                                >دفعة واحدة لكل دراسة</span
                            >
                        </p>
                        <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                            <li
                                v-for="item in includes"
                                :key="item"
                                class="flex items-start gap-2.5 text-sm"
                            >
                                <span
                                    class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground"
                                >
                                    <Check class="size-3" />
                                </span>
                                {{ item }}
                            </li>
                            <li class="flex items-start gap-2.5 text-sm">
                                <span
                                    class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground"
                                >
                                    <Check class="size-3" />
                                </span>
                                {{ draftDurationDays }} يومًا لإكمال بياناتك
                            </li>
                        </ul>
                        <Button
                            size="lg"
                            class="group mt-10 h-12 w-full px-8 text-base shadow-lg shadow-primary/25 transition-all hover:-translate-y-0.5 sm:w-auto"
                            as-child
                        >
                            <Link :href="createStudy()">
                                ابدأ دراسة الجدوى
                                <ArrowLeft
                                    class="size-4 transition-transform group-hover:-translate-x-1"
                                />
                            </Link>
                        </Button>
                    </div>

                    <div
                        class="relative border-t bg-gradient-to-br from-ink to-ink/95 p-8 text-white sm:p-10 lg:col-span-2 lg:border-s lg:border-t-0"
                    >
                        <div
                            class="bg-grid-pattern absolute inset-0 opacity-20 [--foreground:white]"
                            aria-hidden="true"
                        ></div>
                        <div class="relative">
                            <p class="font-display text-lg font-bold">
                                الدفع بالتحويل البنكي
                            </p>
                            <p
                                class="mt-2 text-sm leading-relaxed text-white/70"
                            >
                                ابدأ إدخال بياناتك فورًا دون دفع. اعتماد الدفع
                                مطلوب فقط لإنهاء الدراسة وإصدار التقرير.
                            </p>
                            <ol class="mt-8 space-y-6">
                                <li
                                    v-for="(step, index) in paymentSteps"
                                    :key="step.title"
                                    class="relative flex gap-4"
                                >
                                    <span
                                        v-if="index < paymentSteps.length - 1"
                                        class="absolute start-5 top-10 h-[calc(100%-1rem)] w-px bg-white/15"
                                        aria-hidden="true"
                                    ></span>
                                    <span
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-gold ring-1 ring-white/15"
                                    >
                                        <component
                                            :is="step.icon"
                                            class="size-5"
                                        />
                                    </span>
                                    <span>
                                        <span class="block font-semibold">{{
                                            step.title
                                        }}</span>
                                        <span
                                            class="block text-sm text-white/60"
                                            >{{ step.text }}</span
                                        >
                                    </span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>
</template>
