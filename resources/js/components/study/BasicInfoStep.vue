<script setup lang="ts">
import { computed } from 'vue';
import NativeSelect from '@/components/study/NativeSelect.vue';
import NumberInput from '@/components/study/NumberInput.vue';
import RepeatableGroup from '@/components/study/RepeatableGroup.vue';
import StepCard from '@/components/study/StepCard.vue';
import StudyField from '@/components/study/StudyField.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { newRowKey, useStudyForm } from '@/composables/useStudyForm';
import { formatNumber } from '@/lib/format';
import type { OptionItem } from '@/types';

const { form, options, error } = useStudyForm();

const decimalOptions: OptionItem<number>[] = options.decimals.map((places) => ({
    value: places,
    label:
        places === 0
            ? 'بدون أعشار (1,250)'
            : `${places} خانات (${(1250).toFixed(places)})`,
}));

const vatOptions: OptionItem<number>[] = options.vatPercents.map((percent) => ({
    value: percent,
    label: percent === 0 ? 'لا توجد ضريبة قيمة مضافة' : `${percent}%`,
}));

const yearOptions: OptionItem<number>[] = options.studyYears.map((years) => ({
    value: years,
    label: years === 1 ? 'سنة واحدة' : years === 2 ? 'سنتان' : `${years} سنوات`,
}));

const totalShare = computed(() =>
    form.owners.reduce((sum, owner) => sum + (Number(owner.share) || 0), 0),
);

function addOwner(): void {
    form.owners.push({
        key: newRowKey(),
        name: null,
        share: null,
        experience: null,
    });
}
</script>

<template>
    <div class="space-y-6">
        <StepCard
            title="هوية الدراسة"
            description="اسم الدراسة والمشروع وتصنيفه وتاريخ بدايته."
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <StudyField
                    id="title"
                    label="اسم الدراسة"
                    :error="error('title')"
                >
                    <Input id="title" v-model="form.title" class="h-10" />
                </StudyField>
                <StudyField
                    id="project_name"
                    label="اسم المشروع"
                    :error="error('project_name')"
                >
                    <Input
                        id="project_name"
                        v-model="form.project_name"
                        class="h-10"
                    />
                </StudyField>
                <StudyField
                    id="sector"
                    label="القطاع"
                    :error="error('project.sector_code')"
                >
                    <NativeSelect
                        id="sector"
                        v-model="form.project.sector_code"
                        :options="options.sectors"
                        placeholder="اختر القطاع"
                    />
                </StudyField>
                <StudyField
                    id="project_type"
                    label="نوع المشروع"
                    :error="error('project.project_type_code')"
                >
                    <NativeSelect
                        id="project_type"
                        v-model="form.project.project_type_code"
                        :options="options.projectTypes"
                        placeholder="اختر نوع المشروع"
                    />
                </StudyField>
                <StudyField
                    v-if="form.project.project_type_code === 'other'"
                    id="project_type_other"
                    label="صف نوع المشروع"
                    :error="error('project.project_type_other')"
                >
                    <Input
                        id="project_type_other"
                        v-model="form.project.project_type_other"
                        class="h-10"
                    />
                </StudyField>
                <StudyField
                    id="start_date"
                    label="تاريخ بدء المشروع"
                    :error="error('project.start_date')"
                >
                    <Input
                        id="start_date"
                        v-model="form.project.start_date"
                        type="date"
                        class="h-10"
                    />
                </StudyField>
            </div>
        </StepCard>

        <StepCard
            title="الخيارات المحاسبية"
            description="العملة وخانات الأعشار ونسبة ضريبة القيمة المضافة وفق النظام التجاري في بلدك، وعدد سنوات الدراسة."
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <StudyField
                    id="currency"
                    label="العملة"
                    :error="error('settings.currency')"
                >
                    <NativeSelect
                        id="currency"
                        v-model="form.settings.currency"
                        :options="options.currencies"
                    />
                </StudyField>
                <StudyField
                    id="decimals"
                    label="خانات الأعشار"
                    :error="error('settings.decimals')"
                >
                    <NativeSelect
                        id="decimals"
                        v-model="form.settings.decimals"
                        :options="decimalOptions"
                    />
                </StudyField>
                <StudyField
                    id="vat_percent"
                    label="نسبة ضريبة القيمة المضافة"
                    :error="error('settings.vat_percent')"
                >
                    <NativeSelect
                        id="vat_percent"
                        v-model="form.settings.vat_percent"
                        :options="vatOptions"
                        placeholder="اختر النسبة"
                    />
                </StudyField>
                <StudyField
                    id="study_years"
                    label="مدة الدراسة"
                    hint="عدد السنوات التي تُستخرج لها الأرقام والقوائم المالية."
                    :error="error('settings.study_years')"
                >
                    <NativeSelect
                        id="study_years"
                        v-model="form.settings.study_years"
                        :options="yearOptions"
                        placeholder="اختر المدة"
                    />
                </StudyField>
            </div>
        </StepCard>

        <StepCard title="عن المشروع">
            <div class="grid gap-5">
                <StudyField
                    id="description"
                    label="نبذة عن المشروع"
                    :error="error('project.description')"
                >
                    <Textarea
                        id="description"
                        v-model="form.project.description"
                        placeholder="ما الذي يقدمه المشروع؟ ولمن؟ وأين سيعمل؟"
                    />
                </StudyField>
                <StudyField
                    id="organizational_feasibility"
                    label="الجدوى التنظيمية"
                    hint="تحدث عن خبرات الملاك، والهيكل التنظيمي لإدارة المشروع، وسياسة العمل وآليات التطبيق."
                    :error="error('project.organizational_feasibility')"
                >
                    <Textarea
                        id="organizational_feasibility"
                        v-model="form.project.organizational_feasibility"
                    />
                </StudyField>
            </div>
        </StepCard>

        <StepCard>
            <RepeatableGroup
                title="الملاك"
                description="جميع المساهمين في المشروع ونسبة ملكية كل منهم. 40 تعني 40% من ملكية المشروع."
                :rows="form.owners"
                add-label="إضافة مالك"
                empty-text="لم تُضف أي مالك بعد."
                row-label="المالك"
                :error="error('owners')"
                @add="addOwner"
                @remove="form.owners.splice($event, 1)"
            >
                <template #default="{ index }">
                    <div class="grid gap-4 sm:grid-cols-[2fr_1fr]">
                        <StudyField
                            :id="`owner-name-${index}`"
                            label="الاسم الكامل"
                            :error="error(`owners.${index}.name`)"
                        >
                            <Input
                                :id="`owner-name-${index}`"
                                v-model="form.owners[index].name"
                                class="h-10"
                            />
                        </StudyField>
                        <StudyField
                            :id="`owner-share-${index}`"
                            label="نسبة الملكية"
                            :error="error(`owners.${index}.share`)"
                        >
                            <NumberInput
                                :id="`owner-share-${index}`"
                                v-model="form.owners[index].share"
                                suffix="%"
                            />
                        </StudyField>
                        <StudyField
                            :id="`owner-experience-${index}`"
                            label="الخبرات ذات الصلة"
                            class="sm:col-span-2"
                            :error="error(`owners.${index}.experience`)"
                        >
                            <Textarea
                                :id="`owner-experience-${index}`"
                                v-model="form.owners[index].experience"
                                class="min-h-16"
                            />
                        </StudyField>
                    </div>
                </template>
            </RepeatableGroup>
            <p
                v-if="form.owners.length > 0"
                class="mt-4 text-sm"
                :class="
                    totalShare > 100
                        ? 'text-red-600 dark:text-red-400'
                        : 'text-muted-foreground'
                "
            >
                مجموع الحصص: {{ formatNumber(totalShare, 2) }}%
                <span v-if="totalShare > 100">· يجب ألا يتجاوز 100%</span>
            </p>
        </StepCard>
    </div>
</template>
