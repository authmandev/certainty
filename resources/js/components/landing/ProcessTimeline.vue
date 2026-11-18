<script setup lang="ts">
import {
    BadgeCheck,
    Banknote,
    ClipboardPenLine,
    FileDown,
    UserRoundPlus,
} from '@lucide/vue';
import { computed, ref, useTemplateRef } from 'vue';
import Reveal from '@/components/landing/Reveal.vue';
import SectionHeading from '@/components/landing/SectionHeading.vue';
import { useInView } from '@/composables/useInView';

const { draftDurationDays, formattedPrice } = defineProps<{
    draftDurationDays: number;
    formattedPrice: string;
}>();

const steps = computed(() => [
    {
        icon: UserRoundPlus,
        title: 'أنشئ حسابك',
        description: 'سجّل بالاسم والبريد الإلكتروني وكلمة المرور خلال دقيقة.',
    },
    {
        icon: ClipboardPenLine,
        title: 'أدخل بيانات مشروعك',
        description: `خمس مراحل موجّهة مع ملخص مالي مباشر، ولديك ${draftDurationDays} يومًا لإكمالها.`,
    },
    {
        icon: Banknote,
        title: 'حوّل الرسوم بنكيًا',
        description: `حوّل ${formattedPrice} واكتب رقم مرجع الدفع، ثم ارفع صورة الإيصال.`,
    },
    {
        icon: BadgeCheck,
        title: 'نراجع التحويل',
        description:
            'يراجع فريقنا الإيصال ويعتمد الدفع، وتستطيع متابعة الإدخال خلال ذلك.',
    },
    {
        icon: FileDown,
        title: 'احصل على تقريرك',
        description: 'أنهِ الدراسة بعد اعتماد الدفع وحمّل تقرير PDF النهائي.',
    },
]);

const activeStep = ref(0);
const track = useTemplateRef<HTMLElement>('track');
const trackInView = useInView(track);
</script>

<template>
    <section
        id="how-it-works"
        class="relative scroll-mt-20 overflow-hidden border-y bg-muted/30 py-24 sm:py-32"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <SectionHeading
                eyebrow="كيف تعمل"
                title="من الفكرة إلى تقرير جدوى نهائي في خمس خطوات"
                description="رحلة واضحة من التسجيل حتى استلام التقرير، مع إمكانية إدخال بياناتك قبل اعتماد الدفع."
            />

            <div ref="track" class="relative mt-16">
                <!-- Connector line drawn from the first step (right) towards the last -->
                <div
                    class="absolute inset-x-[10%] top-7 hidden h-0.5 bg-border lg:block"
                    aria-hidden="true"
                >
                    <div
                        class="h-full origin-right bg-gradient-to-l from-primary to-gold transition-transform duration-[1600ms] ease-out"
                        :class="trackInView ? 'scale-x-100' : 'scale-x-0'"
                    ></div>
                </div>

                <ol class="relative grid gap-4 lg:grid-cols-5 lg:gap-6">
                    <Reveal
                        v-for="(step, index) in steps"
                        :key="step.title"
                        as="li"
                        :delay="index * 120"
                    >
                        <button
                            type="button"
                            class="group flex w-full items-start gap-4 rounded-2xl p-3 text-start transition-colors hover:bg-background lg:flex-col lg:items-center lg:text-center"
                            :class="
                                activeStep === index
                                    ? 'bg-background shadow-sm'
                                    : ''
                            "
                            :aria-pressed="activeStep === index"
                            @click="activeStep = index"
                            @mouseenter="activeStep = index"
                            @focus="activeStep = index"
                        >
                            <span
                                class="relative flex size-14 shrink-0 items-center justify-center rounded-2xl border bg-card shadow-sm transition-all duration-300 group-hover:-translate-y-1"
                                :class="
                                    activeStep === index
                                        ? 'border-primary bg-primary text-primary-foreground shadow-lg shadow-primary/30'
                                        : 'text-primary'
                                "
                            >
                                <component :is="step.icon" class="size-6" />
                                <span
                                    class="absolute -end-2 -top-2 flex size-6 items-center justify-center rounded-full border bg-background font-display text-xs font-bold text-foreground"
                                    >{{ index + 1 }}</span
                                >
                            </span>
                            <span class="lg:mt-3">
                                <span class="block font-display font-bold">{{
                                    step.title
                                }}</span>
                                <span
                                    class="mt-1.5 block text-sm leading-relaxed text-muted-foreground"
                                    >{{ step.description }}</span
                                >
                            </span>
                        </button>
                    </Reveal>
                </ol>
            </div>

            <!-- Visual story for the active step -->
            <Reveal class="mx-auto mt-12 max-w-3xl">
                <div
                    class="relative min-h-52 overflow-hidden rounded-3xl border bg-card p-6 shadow-xl shadow-primary/5 sm:p-8"
                >
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 translate-y-3"
                        leave-active-class="transition duration-200 ease-in"
                        leave-to-class="opacity-0 -translate-y-3"
                    >
                        <div
                            v-if="activeStep === 0"
                            key="0"
                            class="mx-auto max-w-sm space-y-3"
                        >
                            <p class="text-sm font-semibold">إنشاء حساب جديد</p>
                            <div
                                class="rounded-lg border px-3 py-2.5 text-sm text-muted-foreground"
                            >
                                الاسم الكامل
                            </div>
                            <div
                                class="rounded-lg border px-3 py-2.5 text-sm text-muted-foreground"
                            >
                                البريد الإلكتروني
                            </div>
                            <div
                                class="rounded-lg bg-primary px-3 py-2.5 text-center text-sm font-medium text-primary-foreground"
                            >
                                إنشاء الحساب
                            </div>
                        </div>

                        <div
                            v-else-if="activeStep === 1"
                            key="1"
                            class="space-y-4"
                        >
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <p class="font-semibold">
                                    المرحلة 3 من 5: التكاليف
                                </p>
                                <span class="text-muted-foreground"
                                    >مكتمل 60%</span
                                >
                            </div>
                            <div class="h-2 rounded-full bg-muted">
                                <div
                                    class="h-full w-3/5 rounded-full bg-primary"
                                ></div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div
                                    v-for="field in [
                                        'آلة القهوة',
                                        'قرض بنكي',
                                        'إيجار شهري',
                                    ]"
                                    :key="field"
                                    class="rounded-xl border p-3 text-sm"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        بند
                                    </p>
                                    <p class="mt-1 font-medium">{{ field }}</p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="activeStep === 2"
                            key="2"
                            class="grid gap-4 sm:grid-cols-2"
                        >
                            <div
                                class="rounded-2xl border border-dashed p-4 text-sm"
                            >
                                <p class="text-xs text-muted-foreground">
                                    رقم مرجع الدفع
                                </p>
                                <p
                                    class="mt-1 font-display text-lg font-bold"
                                    dir="ltr"
                                >
                                    PAY-000042
                                </p>
                                <p class="mt-3 text-xs text-muted-foreground">
                                    المبلغ
                                </p>
                                <p class="font-semibold">
                                    {{ formattedPrice }}
                                </p>
                            </div>
                            <div
                                class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary/40 bg-primary/5 p-4 text-center text-sm"
                            >
                                <Banknote class="size-6 text-primary" />
                                <p class="font-medium">ارفع إيصال التحويل</p>
                                <p class="text-xs text-muted-foreground">
                                    JPG أو PNG أو PDF
                                </p>
                            </div>
                        </div>

                        <div
                            v-else-if="activeStep === 3"
                            key="3"
                            class="mx-auto max-w-md space-y-3"
                        >
                            <div
                                v-for="(status, index) in [
                                    'بانتظار التحويل',
                                    'قيد المراجعة',
                                    'مقبول',
                                ]"
                                :key="status"
                                class="flex items-center gap-3 rounded-xl border p-3 text-sm"
                                :class="
                                    index === 2
                                        ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950'
                                        : ''
                                "
                            >
                                <BadgeCheck
                                    class="size-5"
                                    :class="
                                        index === 2
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-muted-foreground'
                                    "
                                />
                                {{ status }}
                            </div>
                        </div>

                        <div
                            v-else
                            key="4"
                            class="flex flex-col items-center gap-4 text-center sm:flex-row sm:text-start"
                        >
                            <div
                                class="flex h-28 w-22 shrink-0 flex-col justify-between rounded-lg border bg-background p-2 shadow-md"
                                aria-hidden="true"
                            >
                                <div
                                    class="h-1.5 w-2/3 rounded-full bg-primary"
                                ></div>
                                <div class="space-y-1">
                                    <div
                                        class="h-1 rounded-full bg-muted"
                                    ></div>
                                    <div
                                        class="h-1 rounded-full bg-muted"
                                    ></div>
                                    <div
                                        class="h-1 w-1/2 rounded-full bg-muted"
                                    ></div>
                                </div>
                                <div class="flex h-6 items-end gap-0.5">
                                    <div
                                        class="h-2 flex-1 rounded-sm bg-primary/40"
                                    ></div>
                                    <div
                                        class="h-4 flex-1 rounded-sm bg-primary/60"
                                    ></div>
                                    <div
                                        class="h-6 flex-1 rounded-sm bg-primary"
                                    ></div>
                                </div>
                            </div>
                            <div>
                                <p class="font-display text-lg font-bold">
                                    إنهاء الدراسة وإصدار التقرير النهائي
                                </p>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    بعد التأكيد تُحفظ نسخة ثابتة من بياناتك
                                    ونتائجها، ويمكنك تحميل التقرير متى شئت.
                                </p>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Reveal>
        </div>
    </section>
</template>
