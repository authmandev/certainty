<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import NumberInput from '@/components/study/NumberInput.vue';
import RepeatableGroup from '@/components/study/RepeatableGroup.vue';
import StepCard from '@/components/study/StepCard.vue';
import StudyField from '@/components/study/StudyField.vue';
import { Input } from '@/components/ui/input';
import { newRowKey, useStudyForm } from '@/composables/useStudyForm';
import type { ProductRow } from '@/types';

const { form, error, currency } = useStudyForm();

function addProduct(): void {
    form.products.push({
        key: newRowKey(),
        name: null,
        unit_cost: null,
        unit_price: null,
        monthly_quantity: null,
        monthly_capacity: null,
        annual_growth: null,
    });
}

/**
 * Mirrors the legacy rule: a capacity below the expected quantity is treated as equal to the quantity.
 */
function capacityBelowQuantity(product: ProductRow): boolean {
    return (
        product.monthly_capacity !== null &&
        product.monthly_quantity !== null &&
        product.monthly_capacity < product.monthly_quantity
    );
}

function priceBelowCost(product: ProductRow): boolean {
    return (
        product.unit_price !== null &&
        product.unit_cost !== null &&
        product.unit_price < product.unit_cost
    );
}
</script>

<template>
    <StepCard>
        <RepeatableGroup
            title="المنتجات والمبيعات"
            description="لكل منتج أو خدمة: تكلفة المواد للوحدة، وسعر البيع، والكمية المتوقع بيعها شهريًا، والطاقة الإنتاجية، ونسبة النمو السنوي للمبيعات."
            :rows="form.products"
            add-label="إضافة منتج"
            empty-text="أضف أول منتج أو خدمة يقدمها مشروعك."
            row-label="المنتج"
            @add="addProduct"
            @remove="form.products.splice($event, 1)"
        >
            <template #default="{ row, index }">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <StudyField
                        :id="`product-name-${index}`"
                        label="اسم المنتج أو الخدمة"
                        class="sm:col-span-2 lg:col-span-3"
                        :error="error(`products.${index}.name`)"
                    >
                        <Input
                            :id="`product-name-${index}`"
                            v-model="form.products[index].name"
                            class="h-10"
                        />
                    </StudyField>
                    <StudyField
                        :id="`product-cost-${index}`"
                        label="تكلفة المواد للوحدة"
                        hint="شاملة ضريبة القيمة المضافة."
                        :error="error(`products.${index}.unit_cost`)"
                    >
                        <NumberInput
                            :id="`product-cost-${index}`"
                            v-model="form.products[index].unit_cost"
                            :suffix="currency()"
                        />
                    </StudyField>
                    <StudyField
                        :id="`product-price-${index}`"
                        label="سعر بيع الوحدة"
                        hint="قبل ضريبة القيمة المضافة."
                        :error="error(`products.${index}.unit_price`)"
                    >
                        <NumberInput
                            :id="`product-price-${index}`"
                            v-model="form.products[index].unit_price"
                            :suffix="currency()"
                        />
                    </StudyField>
                    <StudyField
                        :id="`product-growth-${index}`"
                        label="نسبة النمو السنوي للمبيعات"
                        :error="error(`products.${index}.annual_growth`)"
                    >
                        <NumberInput
                            :id="`product-growth-${index}`"
                            v-model="form.products[index].annual_growth"
                            suffix="%"
                            :min="-100"
                        />
                    </StudyField>
                    <StudyField
                        :id="`product-quantity-${index}`"
                        label="الكمية المتوقع بيعها شهريًا"
                        :error="error(`products.${index}.monthly_quantity`)"
                    >
                        <NumberInput
                            :id="`product-quantity-${index}`"
                            v-model="form.products[index].monthly_quantity"
                            suffix="وحدة"
                        />
                    </StudyField>
                    <StudyField
                        :id="`product-capacity-${index}`"
                        label="الطاقة الإنتاجية الشهرية"
                        hint="الحد الأقصى للكمية مع نمو المبيعات."
                        :error="error(`products.${index}.monthly_capacity`)"
                    >
                        <NumberInput
                            :id="`product-capacity-${index}`"
                            v-model="form.products[index].monthly_capacity"
                            suffix="وحدة"
                        />
                    </StudyField>
                </div>

                <ul
                    v-if="capacityBelowQuantity(row) || priceBelowCost(row)"
                    class="mt-4 space-y-2 text-sm text-amber-700 dark:text-amber-300"
                >
                    <li
                        v-if="capacityBelowQuantity(row)"
                        class="flex items-start gap-2"
                    >
                        <TriangleAlert class="mt-0.5 size-4 shrink-0" />
                        الطاقة الإنتاجية أقل من الكمية المتوقعة، وستُعتبر مساوية
                        لها في الحسابات.
                    </li>
                    <li
                        v-if="priceBelowCost(row)"
                        class="flex items-start gap-2"
                    >
                        <TriangleAlert class="mt-0.5 size-4 shrink-0" />
                        سعر البيع أقل من تكلفة المواد، وسيحقق المنتج خسارة في كل
                        وحدة.
                    </li>
                </ul>
            </template>
        </RepeatableGroup>
    </StepCard>
</template>
