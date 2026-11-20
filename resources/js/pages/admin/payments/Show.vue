<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CircleCheck, ExternalLink, LoaderCircle, XCircle } from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import ReceiptHistory from '@/components/payment/ReceiptHistory.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatCurrency, formatDateTime } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { approve, index, reject } from '@/routes/admin/payments';
import type { StudyPaymentDetail } from '@/types';

const props = defineProps<{
    payment: { data: StudyPaymentDetail };
    approvedBy: string | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'المدفوعات', href: index() },
        ],
    },
});

const payment = computed(() => props.payment.data);
const receipts = computed(() => payment.value.receipts ?? []);
const receiptUnderReview = computed(() =>
    receipts.value.find((receipt) => receipt.status.value === 'submitted'),
);
const canReview = computed(
    () =>
        payment.value.status.value === 'under_review' &&
        receiptUnderReview.value !== undefined,
);

const approveForm = useForm({});
const rejectForm = useForm({ reason: '' });
const approveOpen = ref(false);
const rejectOpen = ref(false);

function submitApprove(): void {
    approveForm.post(approve(payment.value.id).url, {
        preserveScroll: true,
        onFinish: () => (approveOpen.value = false),
    });
}

function submitReject(): void {
    rejectForm.post(reject(payment.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            rejectOpen.value = false;
            rejectForm.reset();
        },
    });
}
</script>

<template>
    <Head :title="`الدفع ${payment.reference_number}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                :title="`الدفع ${payment.reference_number}`"
                :description="payment.study?.title"
            />
            <PaymentStatusBadge :status="payment.status" />
        </div>

        <InputError
            :message="(approveForm.errors as Record<string, string>).payment"
        />

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <!-- Receipt under review -->
            <section class="min-w-0 rounded-2xl border bg-card">
                <header
                    class="flex flex-wrap items-center justify-between gap-3 border-b p-4"
                >
                    <h2 class="font-semibold">
                        {{
                            receiptUnderReview
                                ? 'الإيصال قيد المراجعة'
                                : 'الإيصال'
                        }}
                    </h2>
                    <a
                        v-if="receiptUnderReview ?? receipts[0]"
                        :href="(receiptUnderReview ?? receipts[0]).url"
                        target="_blank"
                        rel="noopener"
                        class="flex items-center gap-1.5 text-sm text-primary hover:underline"
                    >
                        فتح في نافذة جديدة
                        <ExternalLink class="size-3.5" />
                    </a>
                </header>

                <template v-if="receiptUnderReview ?? receipts[0]">
                    <div class="bg-muted/30 p-4">
                        <img
                            v-if="
                                (
                                    receiptUnderReview ?? receipts[0]
                                ).mime_type.startsWith('image/')
                            "
                            :src="(receiptUnderReview ?? receipts[0]).url"
                            :alt="`إيصال ${payment.reference_number}`"
                            class="mx-auto max-h-[70vh] rounded-lg object-contain"
                        />
                        <iframe
                            v-else
                            :src="(receiptUnderReview ?? receipts[0]).url"
                            :title="`إيصال ${payment.reference_number}`"
                            class="h-[70vh] w-full rounded-lg border bg-background"
                        ></iframe>
                    </div>
                </template>
                <p
                    v-else
                    class="p-10 text-center text-sm text-muted-foreground"
                >
                    لم يرفع العميل أي إيصال بعد.
                </p>

                <footer
                    v-if="canReview"
                    class="flex flex-wrap items-center justify-end gap-2 border-t p-4"
                >
                    <Button variant="outline" @click="rejectOpen = true">
                        <XCircle class="size-4" />
                        رفض
                    </Button>
                    <Button @click="approveOpen = true">
                        <CircleCheck class="size-4" />
                        قبول الدفع
                    </Button>
                </footer>
            </section>

            <!-- Details and history -->
            <aside class="space-y-6">
                <section class="rounded-2xl border bg-card p-5 text-sm">
                    <h2 class="mb-3 font-semibold">التفاصيل</h2>
                    <dl class="divide-y">
                        <div class="flex justify-between gap-3 py-2">
                            <dt class="text-muted-foreground">
                                المبلغ المطلوب
                            </dt>
                            <dd class="font-semibold tabular-nums">
                                {{
                                    formatCurrency(
                                        payment.amount,
                                        payment.currency,
                                    )
                                }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2">
                            <dt class="text-muted-foreground">مرجع الدفع</dt>
                            <dd class="font-medium">
                                <span dir="ltr">{{
                                    payment.reference_number
                                }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2">
                            <dt class="text-muted-foreground">العميل</dt>
                            <dd class="min-w-0 text-end">
                                <p class="font-medium">
                                    {{ payment.customer?.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ payment.customer?.email }}
                                </p>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2">
                            <dt class="text-muted-foreground">الدراسة</dt>
                            <dd class="min-w-0 text-end">
                                <p class="truncate font-medium">
                                    {{ payment.study?.title }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ payment.study?.reference }} ·
                                    {{ payment.study?.completion_percentage }}%
                                    مكتملة
                                </p>
                            </dd>
                        </div>
                        <div
                            v-if="payment.study?.report_url"
                            class="flex justify-between gap-3 py-2"
                        >
                            <dt class="text-muted-foreground">
                                التقرير النهائي
                            </dt>
                            <dd>
                                <a
                                    :href="payment.study.report_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-1 text-primary hover:underline"
                                >
                                    عرض PDF
                                    <ExternalLink class="size-3.5" />
                                </a>
                            </dd>
                        </div>
                        <div
                            v-if="payment.study"
                            class="flex justify-between gap-3 py-2"
                        >
                            <dt class="text-muted-foreground">حالة الدراسة</dt>
                            <dd>
                                <StudyStatusBadge
                                    :status="payment.study.status"
                                />
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2">
                            <dt class="text-muted-foreground">تاريخ الإنشاء</dt>
                            <dd>{{ formatDateTime(payment.created_at) }}</dd>
                        </div>
                        <div
                            v-if="payment.approved_at"
                            class="flex justify-between gap-3 py-2"
                        >
                            <dt class="text-muted-foreground">تاريخ القبول</dt>
                            <dd class="text-end">
                                {{ formatDateTime(payment.approved_at) }}
                                <span
                                    v-if="approvedBy"
                                    class="block text-xs text-muted-foreground"
                                    >بواسطة {{ approvedBy }}</span
                                >
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-2xl border bg-card p-5 text-sm">
                    <h2 class="mb-3 font-semibold">سجل المراجعة</h2>
                    <ReceiptHistory
                        v-if="receipts.length > 0"
                        :receipts="receipts"
                        show-reviewer
                    />
                    <p v-else class="text-muted-foreground">
                        لا توجد إيصالات بعد.
                    </p>
                </section>

                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index()">العودة إلى المدفوعات</Link>
                </Button>
            </aside>
        </div>

        <!-- Approve -->
        <Dialog v-model:open="approveOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>قبول الدفع؟</DialogTitle>
                    <DialogDescription>
                        تأكد من وصول مبلغ
                        {{ formatCurrency(payment.amount, payment.currency) }}
                        إلى الحساب وأن وصف التحويل يتضمن المرجع
                        {{ payment.reference_number }}. لا يمكن التراجع عن
                        القبول.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="outline">إلغاء</Button>
                    </DialogClose>
                    <Button
                        :disabled="approveForm.processing"
                        data-test="confirm-approve"
                        @click="submitApprove"
                    >
                        <LoaderCircle
                            v-if="approveForm.processing"
                            class="size-4 animate-spin"
                        />
                        تأكيد القبول
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Reject -->
        <Dialog v-model:open="rejectOpen">
            <DialogContent>
                <form class="space-y-4" @submit.prevent="submitReject">
                    <DialogHeader>
                        <DialogTitle>رفض الإيصال</DialogTitle>
                        <DialogDescription>
                            سيظهر السبب للعميل في صفحة الدراسة، ويمكنه رفع إيصال
                            جديد.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-2">
                        <Label for="reason">سبب الرفض</Label>
                        <Textarea
                            id="reason"
                            v-model="rejectForm.reason"
                            rows="4"
                            maxlength="1000"
                            placeholder="مثال: المبلغ المحوّل أقل من رسوم الدراسة."
                            required
                        />
                        <InputError :message="rejectForm.errors.reason" />
                        <InputError
                            :message="
                                (rejectForm.errors as Record<string, string>)
                                    .payment
                            "
                        />
                    </div>
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button type="button" variant="outline"
                                >إلغاء</Button
                            >
                        </DialogClose>
                        <Button
                            type="submit"
                            variant="destructive"
                            :disabled="rejectForm.processing"
                        >
                            <LoaderCircle
                                v-if="rejectForm.processing"
                                class="size-4 animate-spin"
                            />
                            رفض الإيصال
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
