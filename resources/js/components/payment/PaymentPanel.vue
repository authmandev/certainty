<script setup lang="ts">
import { CircleCheck, Clock, Info, XCircle } from '@lucide/vue';
import { computed } from 'vue';
import CopyValue from '@/components/payment/CopyValue.vue';
import ReceiptHistory from '@/components/payment/ReceiptHistory.vue';
import ReceiptUploader from '@/components/payment/ReceiptUploader.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import { formatCurrency, formatDate } from '@/lib/format';
import type { BankDetails, ReceiptRules, StudyPaymentDetail } from '@/types';

const { payment, bank } = defineProps<{
    studyId: number;
    payment: StudyPaymentDetail;
    bank: BankDetails;
    receiptRules: ReceiptRules;
}>();

const receipts = computed(() => payment.receipts ?? []);
const lastRejection = computed(
    () =>
        receipts.value.find((receipt) => receipt.status.value === 'rejected')
            ?.rejection_reason ?? null,
);
const hasBankDetails = computed(() => Boolean(bank.iban));
const status = computed(() => payment.status.value);
</script>

<template>
    <section class="space-y-5 rounded-2xl border bg-card p-6 text-sm">
        <div class="flex items-center justify-between gap-2">
            <h2 class="font-semibold">الدفع بالتحويل البنكي</h2>
            <PaymentStatusBadge :status="payment.status" />
        </div>

        <div>
            <p class="text-muted-foreground">رسوم الدراسة</p>
            <p class="mt-1 font-display text-2xl font-bold tabular-nums">
                {{ formatCurrency(payment.amount, payment.currency) }}
            </p>
        </div>

        <!-- What happens next -->
        <div
            v-if="status === 'approved'"
            class="flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200"
        >
            <CircleCheck class="mt-0.5 size-4 shrink-0" />
            <p>
                تم قبول الدفع<template v-if="payment.approved_at">
                    في {{ formatDate(payment.approved_at) }}</template
                >. يمكنك إنهاء الدراسة وتنزيل التقرير بعد اكتمال بياناتها.
            </p>
        </div>
        <div
            v-else-if="status === 'under_review'"
            class="flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200"
        >
            <Clock class="mt-0.5 size-4 shrink-0" />
            <p>
                استلمنا إيصالك وهو قيد المراجعة. يمكنك متابعة إدخال بيانات
                الدراسة في هذه الأثناء.
            </p>
        </div>
        <div
            v-else-if="status === 'rejected'"
            class="flex gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"
            data-test="payment-rejected"
        >
            <XCircle class="mt-0.5 size-4 shrink-0" />
            <div>
                <p class="font-medium">لم نتمكن من قبول الإيصال.</p>
                <p v-if="lastRejection" class="mt-1">
                    السبب: {{ lastRejection }}
                </p>
                <p class="mt-1">صحّح المطلوب ثم ارفع إيصالًا جديدًا.</p>
            </div>
        </div>
        <p v-else class="leading-relaxed text-muted-foreground">
            حوّل المبلغ إلى الحساب التالي، واكتب رقم مرجع الدفع في وصف التحويل،
            ثم ارفع صورة الإيصال. يمكنك إدخال بيانات الدراسة قبل اعتماد الدفع.
        </p>

        <!-- Transfer details -->
        <div v-if="status !== 'approved'" class="rounded-xl border px-3">
            <div class="divide-y">
                <CopyValue
                    label="رقم مرجع الدفع (اكتبه في وصف التحويل)"
                    :value="payment.reference_number"
                />
                <template v-if="hasBankDetails">
                    <CopyValue
                        v-if="bank.name"
                        label="البنك"
                        :value="bank.name"
                    />
                    <CopyValue
                        v-if="bank.account_name"
                        label="اسم الحساب"
                        :value="bank.account_name"
                    />
                    <CopyValue label="رقم الآيبان" :value="bank.iban ?? ''" />
                </template>
                <p
                    v-else
                    class="flex gap-2 py-3 text-muted-foreground"
                    data-test="bank-details-missing"
                >
                    <Info class="mt-0.5 size-4 shrink-0" />
                    ستتوفر بيانات الحساب البنكي هنا قريبًا. تواصل معنا للحصول
                    عليها.
                </p>
            </div>
        </div>

        <ReceiptUploader
            v-if="payment.accepts_receipts"
            :study-id="studyId"
            :rules="receiptRules"
            :replacing="receipts.length > 0"
        />

        <div v-if="receipts.length > 0" class="space-y-3">
            <h3 class="font-medium">الإيصالات المرفوعة</h3>
            <ReceiptHistory :receipts="receipts" />
        </div>
    </section>
</template>
