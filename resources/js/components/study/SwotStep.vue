<script setup lang="ts">
import { ShieldAlert, Sparkles, TrendingDown, TrendingUp } from '@lucide/vue';
import StudyField from '@/components/study/StudyField.vue';
import { Textarea } from '@/components/ui/textarea';
import { useStudyForm } from '@/composables/useStudyForm';

const { form, error } = useStudyForm();

const quadrants = [
    {
        key: 'strengths',
        label: 'نقاط القوة',
        hint: 'ما الذي يميز مشروعك داخليًا؟',
        icon: TrendingUp,
        accent: 'text-emerald-600 bg-emerald-500/10 dark:text-emerald-400',
    },
    {
        key: 'weaknesses',
        label: 'نقاط الضعف',
        hint: 'ما الجوانب الداخلية التي تحتاج إلى تحسين؟',
        icon: TrendingDown,
        accent: 'text-amber-600 bg-amber-500/10 dark:text-amber-400',
    },
    {
        key: 'opportunities',
        label: 'الفرص',
        hint: 'ما الظروف الخارجية التي يمكنك الاستفادة منها؟',
        icon: Sparkles,
        accent: 'text-sky-600 bg-sky-500/10 dark:text-sky-400',
    },
    {
        key: 'threats',
        label: 'المخاطر',
        hint: 'ما التهديدات الخارجية المتوقعة؟',
        icon: ShieldAlert,
        accent: 'text-red-600 bg-red-500/10 dark:text-red-400',
    },
] as const;
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <section
            v-for="quadrant in quadrants"
            :key="quadrant.key"
            class="rounded-2xl border bg-card p-5"
        >
            <div class="mb-4 flex items-center gap-3">
                <span
                    class="flex size-9 items-center justify-center rounded-xl"
                    :class="quadrant.accent"
                >
                    <component :is="quadrant.icon" class="size-4" />
                </span>
                <h3 class="font-display font-bold">{{ quadrant.label }}</h3>
            </div>
            <StudyField
                :id="`swot-${quadrant.key}`"
                :label="quadrant.label"
                :hint="quadrant.hint"
                :error="error(`swot.${quadrant.key}`)"
                class="[&>label]:sr-only"
            >
                <Textarea
                    :id="`swot-${quadrant.key}`"
                    v-model="form.swot[quadrant.key]"
                    class="min-h-36"
                />
            </StudyField>
        </section>
    </div>
</template>
