<script setup lang="ts">
import NumberInput from '@/components/study/NumberInput.vue';
import RepeatableGroup from '@/components/study/RepeatableGroup.vue';
import StepCard from '@/components/study/StepCard.vue';
import StudyField from '@/components/study/StudyField.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { newRowKey, useStudyForm } from '@/composables/useStudyForm';

const { form, error, currency } = useStudyForm();

const narratives = [
    {
        key: 'description',
        label: 'نبذة عن السوق',
        hint: 'حجم السوق ونموه وأبرز ملامحه.',
    },
    {
        key: 'products',
        label: 'المنتجات والخدمات المقدمة',
        hint: 'ما الذي ستقدمه للسوق وما يميزه.',
    },
    {
        key: 'target_customers',
        label: 'الفئة المستهدفة',
        hint: 'من هم عملاؤك المحتملون وما احتياجاتهم.',
    },
    {
        key: 'suppliers',
        label: 'الموردون واعتماد السوق',
        hint: 'مصادر المواد والخدمات التي يعتمد عليها المشروع.',
    },
    {
        key: 'marketing_plan',
        label: 'خطة التسويق',
        hint: 'القنوات والأساليب التي ستصل بها إلى عملائك.',
    },
    {
        key: 'competition_analysis',
        label: 'تحليل المنافسة',
        hint: 'وضع المنافسين في السوق وكيف ستنافسهم.',
    },
] as const;

function addCompetitor(): void {
    form.competitors.push({
        key: newRowKey(),
        name: null,
        share: null,
        characteristics: null,
    });
}
</script>

<template>
    <div class="space-y-6">
        <StepCard title="السوق المستهدف">
            <div class="grid gap-5 sm:grid-cols-2">
                <StudyField
                    id="market_name"
                    label="اسم السوق المستهدف"
                    :error="error('market.name')"
                >
                    <Input
                        id="market_name"
                        v-model="form.market.name"
                        class="h-10"
                        placeholder="مثال: سوق المقاهي المختصة في الرياض"
                    />
                </StudyField>
                <StudyField
                    id="market_size"
                    label="حجم التداول السنوي للسوق"
                    hint="يُستخدم لتقدير حصتك السوقية المتوقعة."
                    :error="error('market.size')"
                >
                    <NumberInput
                        id="market_size"
                        v-model="form.market.size"
                        :suffix="currency()"
                    />
                </StudyField>
            </div>
        </StepCard>

        <StepCard
            title="دراسة السوق"
            description="تظهر هذه الأوصاف في التقرير النهائي كما تكتبها."
        >
            <div class="grid gap-5 md:grid-cols-2">
                <StudyField
                    v-for="item in narratives"
                    :id="`market_${item.key}`"
                    :key="item.key"
                    :label="item.label"
                    :hint="item.hint"
                    :error="error(`market.${item.key}`)"
                >
                    <Textarea
                        :id="`market_${item.key}`"
                        v-model="form.market[item.key]"
                    />
                </StudyField>
            </div>
        </StepCard>

        <StepCard>
            <RepeatableGroup
                title="المنافسون"
                description="أبرز المنافسين وحصتهم التقديرية من السوق (اختياري)."
                :rows="form.competitors"
                add-label="إضافة منافس"
                empty-text="لم تُضف أي منافس بعد."
                row-label="المنافس"
                @add="addCompetitor"
                @remove="form.competitors.splice($event, 1)"
            >
                <template #default="{ index }">
                    <div class="grid gap-4 sm:grid-cols-[2fr_1fr]">
                        <StudyField
                            :id="`competitor-name-${index}`"
                            label="اسم المنافس"
                            :error="error(`competitors.${index}.name`)"
                        >
                            <Input
                                :id="`competitor-name-${index}`"
                                v-model="form.competitors[index].name"
                                class="h-10"
                            />
                        </StudyField>
                        <StudyField
                            :id="`competitor-share-${index}`"
                            label="الحصة السوقية"
                            :error="error(`competitors.${index}.share`)"
                        >
                            <NumberInput
                                :id="`competitor-share-${index}`"
                                v-model="form.competitors[index].share"
                                suffix="%"
                            />
                        </StudyField>
                        <StudyField
                            :id="`competitor-characteristics-${index}`"
                            label="المزايا التنافسية"
                            class="sm:col-span-2"
                            :error="
                                error(`competitors.${index}.characteristics`)
                            "
                        >
                            <Textarea
                                :id="`competitor-characteristics-${index}`"
                                v-model="
                                    form.competitors[index].characteristics
                                "
                                class="min-h-16"
                            />
                        </StudyField>
                    </div>
                </template>
            </RepeatableGroup>
        </StepCard>
    </div>
</template>
