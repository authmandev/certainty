<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, CalendarClock, Eye } from '@lucide/vue';
import { computed, ref, useTemplateRef } from 'vue';
import StudyController from '@/actions/App/Http/Controllers/StudyController';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import BasicInfoStep from '@/components/study/BasicInfoStep.vue';
import CostsStep from '@/components/study/CostsStep.vue';
import MarketStep from '@/components/study/MarketStep.vue';
import SalesStep from '@/components/study/SalesStep.vue';
import SaveStatusIndicator from '@/components/study/SaveStatusIndicator.vue';
import StudyStepper from '@/components/study/StudyStepper.vue';
import SwotStep from '@/components/study/SwotStep.vue';
import { Button } from '@/components/ui/button';
import { useAutosave } from '@/composables/useAutosave';
import { newRowKey, provideStudyForm } from '@/composables/useStudyForm';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { show } from '@/routes/studies';
import type {
    SectionKey,
    Study,
    StudyFormData,
    StudyFormOptions,
    StudyInput,
    StudyProgress,
} from '@/types';

const props = defineProps<{
    study: { data: Study };
    input: StudyInput;
    progress: StudyProgress;
    options: StudyFormOptions;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'دراساتي', href: dashboard() }],
    },
});

const study = computed(() => props.study.data);

/**
 * Rows saved before keys existed get one, so list transitions stay stable.
 */
function withRowKeys(input: StudyInput): StudyInput {
    const rowGroups = [
        'owners',
        'competitors',
        'assets',
        'loans',
        'fixed_costs',
        'employees',
        'products',
    ] as const;
    const copy = JSON.parse(JSON.stringify(input)) as StudyInput;

    for (const group of rowGroups) {
        for (const row of copy[group]) {
            row.key ??= newRowKey();
        }
    }

    return copy;
}

const form = useForm<StudyFormData>({
    title: props.study.data.title,
    project_name: props.study.data.project_name,
    ...withRowKeys(props.input),
});

provideStudyForm({
    form,
    options: props.options,
    currency: () => form.settings.currency ?? '',
    feeAmount: props.study.data.payment?.amount ?? null,
    error: (path) => (form.errors as Record<string, string | undefined>)[path],
});

const { status, lastSavedAt, saveNow } = useAutosave(form, {
    url: () => StudyController.update(study.value.id).url,
    only: ['progress', 'study'],
});

const steps: Record<SectionKey, { component: unknown; description: string }> = {
    basic: {
        component: BasicInfoStep,
        description: 'هوية المشروع وخياراته المحاسبية وملاكه.',
    },
    market: {
        component: MarketStep,
        description: 'السوق المستهدف والمنافسون.',
    },
    costs: {
        component: CostsStep,
        description: 'تكاليف التأسيس والقروض والتكاليف الثابتة والموظفون.',
    },
    sales: {
        component: SalesStep,
        description: 'المنتجات وأسعارها وكميات المبيعات المتوقعة.',
    },
    swot: {
        component: SwotStep,
        description: 'نقاط القوة والضعف والفرص والمخاطر.',
    },
};

const sectionKeys = Object.keys(steps) as SectionKey[];

const currentStep = ref<SectionKey>(
    props.progress.sections.find((section) => !section.is_complete)?.key ??
        'basic',
);

const currentIndex = computed(() => sectionKeys.indexOf(currentStep.value));
const currentSection = computed(() =>
    props.progress.sections.find(
        (section) => section.key === currentStep.value,
    ),
);

const editor = useTemplateRef<HTMLElement>('editor');

function goTo(key: SectionKey): void {
    saveNow();
    currentStep.value = key;
    editor.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function goBy(offset: number): void {
    const next = sectionKeys[currentIndex.value + offset];

    if (next) {
        goTo(next);
    }
}
</script>

<template>
    <Head :title="`تحرير: ${study.title}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Header -->
        <header
            class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border bg-card p-5"
        >
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <StudyStatusBadge :status="study.status" />
                    <PaymentStatusBadge
                        v-if="study.payment"
                        :status="study.payment.status"
                    />
                    <span class="text-xs text-muted-foreground" dir="ltr">{{
                        study.reference
                    }}</span>
                </div>
                <h1 class="mt-2 truncate font-display text-2xl font-bold">
                    {{ form.title || study.title }}
                </h1>
                <p
                    class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                >
                    <span>{{ form.project_name || study.project_name }}</span>
                    <span
                        v-if="study.days_remaining !== null"
                        class="flex items-center gap-1.5"
                    >
                        <CalendarClock class="size-4" />
                        متبقٍ {{ study.days_remaining }} يومًا (حتى
                        {{ formatDate(study.expires_at) }})
                    </span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <SaveStatusIndicator
                    :status="status"
                    :last-saved-at="lastSavedAt"
                    @retry="saveNow"
                />
                <Button variant="outline" size="sm" as-child>
                    <Link :href="show(study.id)">
                        <Eye class="size-4" />
                        عرض الدراسة
                    </Link>
                </Button>
            </div>
        </header>

        <div
            ref="editor"
            class="grid scroll-mt-6 gap-6 lg:grid-cols-[260px_minmax(0,1fr)]"
        >
            <!-- Progress and steps -->
            <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
                <div class="rounded-2xl border bg-card p-4">
                    <div class="flex items-end justify-between">
                        <p class="text-sm text-muted-foreground">
                            نسبة الإكمال
                        </p>
                        <p
                            class="font-display text-2xl font-bold tabular-nums"
                            data-test="completion-percentage"
                        >
                            {{ progress.percentage }}%
                        </p>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-gradient-to-l from-primary to-emerald-500 transition-[width] duration-700 ease-out"
                            :style="{ width: `${progress.percentage}%` }"
                        ></div>
                    </div>
                </div>

                <div class="rounded-2xl border bg-card p-2">
                    <StudyStepper
                        :sections="progress.sections"
                        :current="currentStep"
                        @select="goTo"
                    />
                </div>

                <div
                    v-if="currentSection && currentSection.missing.length > 0"
                    class="hidden rounded-2xl border bg-card p-4 text-sm lg:block"
                >
                    <p class="font-medium">متبقٍ في هذه المرحلة</p>
                    <ul class="mt-2 space-y-1.5 text-muted-foreground">
                        <li
                            v-for="item in currentSection.missing"
                            :key="item"
                            class="flex items-start gap-2"
                        >
                            <span
                                class="mt-2 size-1.5 shrink-0 rounded-full bg-amber-500"
                            ></span>
                            {{ item }}
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Active step -->
            <section class="min-w-0 space-y-6">
                <div>
                    <p class="text-sm font-medium text-primary">
                        المرحلة {{ currentIndex + 1 }} من
                        {{ sectionKeys.length }}
                    </p>
                    <h2 class="mt-1 font-display text-xl font-bold">
                        {{ currentSection?.title }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ steps[currentStep].description }}
                    </p>
                </div>

                <Transition
                    mode="out-in"
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-2"
                    leave-active-class="transition duration-150 ease-in"
                    leave-to-class="opacity-0"
                >
                    <component
                        :is="steps[currentStep].component"
                        :key="currentStep"
                    />
                </Transition>

                <footer
                    class="flex flex-wrap items-center justify-between gap-3 border-t pt-6"
                >
                    <Button
                        v-if="currentIndex > 0"
                        variant="outline"
                        @click="goBy(-1)"
                    >
                        <ArrowRight class="size-4" />
                        السابق
                    </Button>
                    <span v-else></span>
                    <div class="flex gap-2">
                        <Button
                            v-if="currentIndex < sectionKeys.length - 1"
                            @click="goBy(1)"
                        >
                            التالي
                            <ArrowLeft class="size-4" />
                        </Button>
                        <Button v-else as-child>
                            <Link :href="show(study.id)"
                                >إنهاء الإدخال ومراجعة الدراسة</Link
                            >
                        </Button>
                    </div>
                </footer>
            </section>
        </div>
    </div>
</template>
