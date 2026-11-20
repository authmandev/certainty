<script setup lang="ts">
import { computed } from 'vue';
import { formatNumber } from '@/lib/format';
import type { StudyInput } from '@/types';

/**
 * A read-only view of a study's saved input for administrators (BRD FR-039).
 */
const { input, labels } = defineProps<{
    input: StudyInput;
    labels: Record<string, Record<string, string>>;
}>();

function label(group: string, code: string | null): string | null {
    return code ? (labels[group]?.[code] ?? code) : null;
}

function value(item: unknown): string {
    if (item === null || item === undefined || item === '') {
        return '—';
    }

    if (typeof item === 'number') {
        return formatNumber(item, Number.isInteger(item) ? 0 : 2);
    }

    if (typeof item === 'boolean') {
        return item ? 'نعم' : 'لا';
    }

    return String(item);
}

const sections = computed(() => [
    {
        title: 'معلومات أساسية',
        fields: [
            ['العملة', input.settings.currency],
            ['خانات الأعشار', input.settings.decimals],
            [
                'نسبة ضريبة القيمة المضافة',
                input.settings.vat_percent === null
                    ? null
                    : `${input.settings.vat_percent}%`,
            ],
            ['مدة الدراسة (سنوات)', input.settings.study_years],
            ['تاريخ بدء المشروع', input.project.start_date],
            ['القطاع', label('sector', input.project.sector_code)],
            [
                'نوع المشروع',
                input.project.project_type_other ??
                    label('project_type', input.project.project_type_code),
            ],
        ],
        texts: [
            ['نبذة عن المشروع', input.project.description],
            ['الجدوى التنظيمية', input.project.organizational_feasibility],
        ],
        tables: [
            {
                title: 'الملاك',
                headers: ['الاسم', 'الحصة %', 'الخبرة'],
                rows: input.owners.map((row) => [
                    row.name,
                    row.share,
                    row.experience,
                ]),
            },
        ],
    },
    {
        title: 'دراسة السوق',
        fields: [
            ['السوق المستهدف', input.market.name],
            ['حجم السوق', input.market.size],
        ],
        texts: [
            ['نبذة عن السوق', input.market.description],
            ['المنتجات والخدمات', input.market.products],
            ['الفئة المستهدفة', input.market.target_customers],
            ['الموردون', input.market.suppliers],
            ['خطة التسويق', input.market.marketing_plan],
            ['تحليل المنافسة', input.market.competition_analysis],
        ],
        tables: [
            {
                title: 'المنافسون',
                headers: ['الاسم', 'الحصة %', 'المزايا'],
                rows: input.competitors.map((row) => [
                    row.name,
                    row.share,
                    row.characteristics,
                ]),
            },
        ],
    },
    {
        title: 'التكاليف',
        fields: [],
        texts: [],
        tables: [
            {
                title: 'تكاليف التأسيس',
                headers: [
                    'الفئة',
                    'الوصف',
                    'القيمة',
                    'العمر',
                    'الاستهلاك %',
                    'شامل الضريبة',
                ],
                rows: input.assets.map((row) => [
                    label('asset_category', row.category_code),
                    row.name,
                    row.value,
                    row.life_span,
                    row.depreciation_rate,
                    row.includes_vat,
                ]),
            },
            {
                title: 'القروض',
                headers: ['الجهة', 'المبلغ', 'الفائدة %', 'السنوات'],
                rows: input.loans.map((row) => [
                    row.lender,
                    row.amount,
                    row.interest_rate,
                    row.years,
                ]),
            },
            {
                title: 'التكاليف الثابتة الشهرية',
                headers: ['الفئة', 'الوصف', 'المبلغ', 'شامل الضريبة'],
                rows: input.fixed_costs.map((row) => [
                    label('fixed_cost_category', row.category_code),
                    row.name,
                    row.monthly_amount,
                    row.includes_vat,
                ]),
            },
            {
                title: 'الموظفون',
                headers: ['الفئة', 'المسمى', 'العدد', 'الراتب الشهري'],
                rows: input.employees.map((row) => [
                    label('job_category', row.job_code),
                    row.title,
                    row.count,
                    row.monthly_salary,
                ]),
            },
        ],
    },
    {
        title: 'المبيعات',
        fields: [],
        texts: [],
        tables: [
            {
                title: 'المنتجات',
                headers: [
                    'المنتج',
                    'تكلفة الوحدة',
                    'سعر الوحدة',
                    'الكمية الشهرية',
                    'الطاقة الشهرية',
                    'النمو السنوي %',
                ],
                rows: input.products.map((row) => [
                    row.name,
                    row.unit_cost,
                    row.unit_price,
                    row.monthly_quantity,
                    row.monthly_capacity,
                    row.annual_growth,
                ]),
            },
        ],
    },
    {
        title: 'تحليل SWOT',
        fields: [],
        texts: [
            ['نقاط القوة', input.swot.strengths],
            ['نقاط الضعف', input.swot.weaknesses],
            ['الفرص', input.swot.opportunities],
            ['التهديدات', input.swot.threats],
        ],
        tables: [],
    },
]);
</script>

<template>
    <div class="space-y-4">
        <details
            v-for="(section, index) in sections"
            :key="section.title"
            class="group rounded-2xl border bg-card"
            :open="index === 0"
        >
            <summary
                class="cursor-pointer list-none p-4 font-semibold select-none"
            >
                <span class="flex items-center justify-between">
                    {{ section.title }}
                    <span
                        class="text-xs font-normal text-muted-foreground group-open:hidden"
                        >عرض</span
                    >
                </span>
            </summary>
            <div class="space-y-5 border-t p-4 text-sm">
                <dl
                    v-if="section.fields.length > 0"
                    class="grid gap-x-6 gap-y-3 sm:grid-cols-2"
                >
                    <div
                        v-for="([name, item], fieldIndex) in section.fields"
                        :key="fieldIndex"
                    >
                        <dt class="text-xs text-muted-foreground">
                            {{ name }}
                        </dt>
                        <dd class="font-medium">{{ value(item) }}</dd>
                    </div>
                </dl>
                <div
                    v-for="([name, text], textIndex) in section.texts"
                    :key="textIndex"
                >
                    <p class="text-xs text-muted-foreground">{{ name }}</p>
                    <p class="mt-0.5 leading-relaxed whitespace-pre-line">
                        {{ value(text) }}
                    </p>
                </div>
                <div v-for="table in section.tables" :key="table.title">
                    <p class="mb-2 font-medium">
                        {{ table.title }}
                        <span class="text-xs font-normal text-muted-foreground"
                            >({{ formatNumber(table.rows.length) }})</span
                        >
                    </p>
                    <div
                        v-if="table.rows.length > 0"
                        class="overflow-x-auto rounded-lg border"
                    >
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 text-muted-foreground">
                                <tr>
                                    <th
                                        v-for="header in table.headers"
                                        :key="header"
                                        class="px-3 py-2 text-start font-medium"
                                    >
                                        {{ header }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="(row, rowIndex) in table.rows"
                                    :key="rowIndex"
                                >
                                    <td
                                        v-for="(cell, cellIndex) in row"
                                        :key="cellIndex"
                                        class="px-3 py-2 tabular-nums"
                                    >
                                        {{ value(cell) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-xs text-muted-foreground">
                        لا توجد بيانات.
                    </p>
                </div>
            </div>
        </details>
    </div>
</template>
