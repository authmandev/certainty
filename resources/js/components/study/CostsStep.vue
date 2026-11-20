<script setup lang="ts">
import { Lock } from '@lucide/vue';
import NativeSelect from '@/components/study/NativeSelect.vue';
import NumberInput from '@/components/study/NumberInput.vue';
import RepeatableGroup from '@/components/study/RepeatableGroup.vue';
import StepCard from '@/components/study/StepCard.vue';
import StudyField from '@/components/study/StudyField.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { newRowKey, useStudyForm } from '@/composables/useStudyForm';
import { formatNumber } from '@/lib/format';

const { form, options, error, currency, feeAmount } = useStudyForm();

function addAsset(): void {
    form.assets.push({
        key: newRowKey(),
        category_code: null,
        name: null,
        value: null,
        life_span: null,
        depreciation_rate: null,
        includes_vat: false,
    });
}

function addLoan(): void {
    form.loans.push({
        key: newRowKey(),
        lender: null,
        amount: null,
        interest_rate: null,
        years: null,
    });
}

function addFixedCost(): void {
    form.fixed_costs.push({
        key: newRowKey(),
        category_code: null,
        name: null,
        monthly_amount: null,
        includes_vat: false,
    });
}

function addEmployee(): void {
    form.employees.push({
        key: newRowKey(),
        job_code: null,
        title: null,
        count: null,
        monthly_salary: null,
    });
}
</script>

<template>
    <div class="space-y-6">
        <StepCard>
            <RepeatableGroup
                title="تكاليف التأسيس والإنشاء"
                description="الأصول والتجهيزات اللازمة لبدء المشروع، مع عمرها الافتراضي ونسبة استهلاكها السنوية."
                :rows="form.assets"
                add-label="إضافة أصل"
                empty-text="أضف أول أصل تأسيسي لمشروعك."
                row-label="الأصل"
                @add="addAsset"
                @remove="form.assets.splice($event, 1)"
            >
                <template #default="{ index }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <StudyField
                            :id="`asset-category-${index}`"
                            label="فئة الأصل"
                            :error="error(`assets.${index}.category_code`)"
                        >
                            <NativeSelect
                                :id="`asset-category-${index}`"
                                v-model="form.assets[index].category_code"
                                :options="options.assetCategories"
                            />
                        </StudyField>
                        <StudyField
                            :id="`asset-name-${index}`"
                            label="وصف الأصل"
                            :hint="
                                form.assets[index].category_code === 'other'
                                    ? 'مطلوب عند اختيار «أخرى».'
                                    : 'اختياري'
                            "
                            :error="error(`assets.${index}.name`)"
                        >
                            <Input
                                :id="`asset-name-${index}`"
                                v-model="form.assets[index].name"
                                class="h-10"
                            />
                        </StudyField>
                        <StudyField
                            :id="`asset-value-${index}`"
                            label="القيمة"
                            :error="error(`assets.${index}.value`)"
                        >
                            <NumberInput
                                :id="`asset-value-${index}`"
                                v-model="form.assets[index].value"
                                :suffix="currency()"
                            />
                        </StudyField>
                        <StudyField
                            :id="`asset-life-${index}`"
                            label="العمر الافتراضي"
                            hint="0 يعني أن الأصل لا يُستبدل."
                            :error="error(`assets.${index}.life_span`)"
                        >
                            <NumberInput
                                :id="`asset-life-${index}`"
                                v-model="form.assets[index].life_span"
                                suffix="سنوات"
                                step="1"
                            />
                        </StudyField>
                        <StudyField
                            :id="`asset-depreciation-${index}`"
                            label="نسبة الاستهلاك السنوية"
                            :error="error(`assets.${index}.depreciation_rate`)"
                        >
                            <NumberInput
                                :id="`asset-depreciation-${index}`"
                                v-model="form.assets[index].depreciation_rate"
                                suffix="%"
                            />
                        </StudyField>
                        <div class="flex items-end pb-2.5">
                            <Label
                                :for="`asset-vat-${index}`"
                                class="flex cursor-pointer items-center gap-2.5 font-normal"
                            >
                                <Checkbox
                                    :id="`asset-vat-${index}`"
                                    v-model="form.assets[index].includes_vat"
                                />
                                خاضع لضريبة القيمة المضافة
                            </Label>
                        </div>
                    </div>
                </template>
            </RepeatableGroup>

            <div
                v-if="feeAmount"
                class="mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-4 text-sm"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <Lock class="size-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium">رسوم إعداد دراسة الجدوى</p>
                    <p class="text-xs text-muted-foreground">
                        تُضاف تلقائيًا إلى تكاليف التأسيس مرة واحدة، دون استهلاك
                        أو استبدال، ولا يمكن تعديلها.
                    </p>
                </div>
                <span class="font-display font-bold tabular-nums"
                    >{{ formatNumber(feeAmount) }} {{ currency() }}</span
                >
            </div>
        </StepCard>

        <StepCard>
            <RepeatableGroup
                title="القروض والمديونيات"
                description="التمويل المقترض لتأسيس المشروع. يُحسب القسط الشهري ويُضاف إلى التكاليف تلقائيًا (اختياري)."
                :rows="form.loans"
                add-label="إضافة قرض"
                empty-text="لا توجد قروض. أضف قرضًا إذا كان المشروع سيُموَّل جزئيًا بالاقتراض."
                row-label="القرض"
                @add="addLoan"
                @remove="form.loans.splice($event, 1)"
            >
                <template #default="{ index }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <StudyField
                            :id="`loan-lender-${index}`"
                            label="الجهة المقرضة"
                            :error="error(`loans.${index}.lender`)"
                        >
                            <Input
                                :id="`loan-lender-${index}`"
                                v-model="form.loans[index].lender"
                                class="h-10"
                            />
                        </StudyField>
                        <StudyField
                            :id="`loan-amount-${index}`"
                            label="قيمة القرض"
                            :error="error(`loans.${index}.amount`)"
                        >
                            <NumberInput
                                :id="`loan-amount-${index}`"
                                v-model="form.loans[index].amount"
                                :suffix="currency()"
                            />
                        </StudyField>
                        <StudyField
                            :id="`loan-interest-${index}`"
                            label="الفائدة السنوية"
                            :error="error(`loans.${index}.interest_rate`)"
                        >
                            <NumberInput
                                :id="`loan-interest-${index}`"
                                v-model="form.loans[index].interest_rate"
                                suffix="%"
                            />
                        </StudyField>
                        <StudyField
                            :id="`loan-years-${index}`"
                            label="سنوات السداد"
                            :error="error(`loans.${index}.years`)"
                        >
                            <NumberInput
                                :id="`loan-years-${index}`"
                                v-model="form.loans[index].years"
                                suffix="سنوات"
                                step="1"
                                :min="1"
                            />
                        </StudyField>
                    </div>
                </template>
            </RepeatableGroup>
        </StepCard>

        <StepCard>
            <RepeatableGroup
                title="التكاليف الشهرية الثابتة"
                description="المصاريف التي تتكرر كل شهر بغض النظر عن حجم المبيعات (اختياري)."
                :rows="form.fixed_costs"
                add-label="إضافة تكلفة"
                empty-text="لم تُضف أي تكلفة ثابتة بعد."
                row-label="التكلفة"
                @add="addFixedCost"
                @remove="form.fixed_costs.splice($event, 1)"
            >
                <template #default="{ index }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <StudyField
                            :id="`fixed-category-${index}`"
                            label="فئة التكلفة"
                            :error="error(`fixed_costs.${index}.category_code`)"
                        >
                            <NativeSelect
                                :id="`fixed-category-${index}`"
                                v-model="form.fixed_costs[index].category_code"
                                :options="options.fixedCostCategories"
                            />
                        </StudyField>
                        <StudyField
                            :id="`fixed-name-${index}`"
                            label="الوصف"
                            :hint="
                                form.fixed_costs[index].category_code ===
                                'other'
                                    ? 'مطلوب عند اختيار «أخرى».'
                                    : 'اختياري'
                            "
                            :error="error(`fixed_costs.${index}.name`)"
                        >
                            <Input
                                :id="`fixed-name-${index}`"
                                v-model="form.fixed_costs[index].name"
                                class="h-10"
                            />
                        </StudyField>
                        <StudyField
                            :id="`fixed-amount-${index}`"
                            label="المبلغ الشهري"
                            :error="
                                error(`fixed_costs.${index}.monthly_amount`)
                            "
                        >
                            <NumberInput
                                :id="`fixed-amount-${index}`"
                                v-model="form.fixed_costs[index].monthly_amount"
                                :suffix="currency()"
                            />
                        </StudyField>
                        <div class="flex items-end pb-2.5">
                            <Label
                                :for="`fixed-vat-${index}`"
                                class="flex cursor-pointer items-center gap-2.5 font-normal"
                            >
                                <Checkbox
                                    :id="`fixed-vat-${index}`"
                                    v-model="
                                        form.fixed_costs[index].includes_vat
                                    "
                                />
                                شامل ضريبة القيمة المضافة
                            </Label>
                        </div>
                    </div>
                </template>
            </RepeatableGroup>
        </StepCard>

        <StepCard>
            <RepeatableGroup
                title="الموظفون"
                description="الوظائف وعدد الموظفين في كل منها والراتب الشهري للموظف الواحد (اختياري)."
                :rows="form.employees"
                add-label="إضافة وظيفة"
                empty-text="لم تُضف أي وظيفة بعد."
                row-label="الوظيفة"
                @add="addEmployee"
                @remove="form.employees.splice($event, 1)"
            >
                <template #default="{ index }">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <StudyField
                            :id="`employee-job-${index}`"
                            label="الفئة الوظيفية"
                            :error="error(`employees.${index}.job_code`)"
                        >
                            <NativeSelect
                                :id="`employee-job-${index}`"
                                v-model="form.employees[index].job_code"
                                :options="options.jobCategories"
                            />
                        </StudyField>
                        <StudyField
                            :id="`employee-title-${index}`"
                            label="المسمى الوظيفي"
                            :hint="
                                form.employees[index].job_code === 'other'
                                    ? 'مطلوب عند اختيار «أخرى».'
                                    : 'اختياري'
                            "
                            :error="error(`employees.${index}.title`)"
                        >
                            <Input
                                :id="`employee-title-${index}`"
                                v-model="form.employees[index].title"
                                class="h-10"
                            />
                        </StudyField>
                        <StudyField
                            :id="`employee-count-${index}`"
                            label="العدد"
                            :error="error(`employees.${index}.count`)"
                        >
                            <NumberInput
                                :id="`employee-count-${index}`"
                                v-model="form.employees[index].count"
                                step="1"
                                :min="1"
                            />
                        </StudyField>
                        <StudyField
                            :id="`employee-salary-${index}`"
                            label="الراتب الشهري"
                            :error="error(`employees.${index}.monthly_salary`)"
                        >
                            <NumberInput
                                :id="`employee-salary-${index}`"
                                v-model="form.employees[index].monthly_salary"
                                :suffix="currency()"
                            />
                        </StudyField>
                    </div>
                </template>
            </RepeatableGroup>
        </StepCard>
    </div>
</template>
