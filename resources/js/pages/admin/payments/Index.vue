<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Search } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatCurrency, formatDateTime, formatNumber } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index, show } from '@/routes/admin/payments';
import type {
    OptionItem,
    Paginated,
    PaymentStatus,
    StudyPaymentDetail,
} from '@/types';

const props = defineProps<{
    payments: Paginated<StudyPaymentDetail>;
    filters: { status: PaymentStatus | null; search: string | null };
    statusCounts: Partial<Record<PaymentStatus, number>>;
    statuses: OptionItem<PaymentStatus>[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'المدفوعات', href: index() },
        ],
    },
});

const search = ref(props.filters.search ?? '');

function visit(status: PaymentStatus | null): void {
    router.get(
        index.url({
            query: {
                status: status ?? undefined,
                search: search.value.trim() || undefined,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const onSearch = useDebounceFn(() => visit(props.filters.status), 350);

const total = computed(() =>
    Object.values(props.statusCounts).reduce(
        (sum, count) => sum + (count ?? 0),
        0,
    ),
);
</script>

<template>
    <Head title="المدفوعات" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="المدفوعات"
            description="مراجعة إيصالات التحويل البنكي واعتماد الدفعات"
        />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div
                class="flex flex-wrap gap-1 rounded-xl bg-muted p-1 text-sm"
                role="tablist"
                aria-label="تصفية حسب الحالة"
            >
                <button
                    type="button"
                    role="tab"
                    class="rounded-lg px-3 py-1.5 transition-colors"
                    :class="
                        filters.status === null
                            ? 'bg-background font-medium shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    :aria-selected="filters.status === null"
                    @click="visit(null)"
                >
                    الكل ({{ formatNumber(total) }})
                </button>
                <button
                    v-for="status in statuses"
                    :key="status.value"
                    type="button"
                    role="tab"
                    class="rounded-lg px-3 py-1.5 transition-colors"
                    :class="
                        filters.status === status.value
                            ? 'bg-background font-medium shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    :aria-selected="filters.status === status.value"
                    @click="visit(status.value)"
                >
                    {{ status.label }}
                    ({{ formatNumber(statusCounts[status.value] ?? 0) }})
                </button>
            </div>

            <div class="relative w-full sm:w-72">
                <Search
                    class="pointer-events-none absolute inset-s-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    type="search"
                    class="ps-9"
                    placeholder="رقم المرجع أو الدراسة أو العميل"
                    aria-label="بحث"
                    @update:model-value="onSearch"
                />
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border bg-card">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="border-b bg-muted/40 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">
                            مرجع الدفع
                        </th>
                        <th class="px-4 py-3 text-start font-medium">العميل</th>
                        <th class="px-4 py-3 text-start font-medium">
                            الدراسة
                        </th>
                        <th class="px-4 py-3 text-start font-medium">المبلغ</th>
                        <th class="px-4 py-3 text-start font-medium">
                            آخر إيصال
                        </th>
                        <th class="px-4 py-3 text-start font-medium">الحالة</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="payment in payments.data"
                        :key="payment.id"
                        class="transition-colors hover:bg-muted/40"
                    >
                        <td class="px-4 py-3 font-medium">
                            <span dir="ltr">{{
                                payment.reference_number
                            }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium">
                                {{ payment.customer?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ payment.customer?.email }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="max-w-56 truncate">
                                {{ payment.study?.title }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ payment.study?.reference }}
                            </p>
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            {{
                                formatCurrency(payment.amount, payment.currency)
                            }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                payment.latest_receipt
                                    ? formatDateTime(
                                          payment.latest_receipt.submitted_at,
                                      )
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <PaymentStatusBadge :status="payment.status" />
                        </td>
                        <td class="px-4 py-3 text-end">
                            <Button
                                size="sm"
                                :variant="
                                    payment.status.value === 'under_review'
                                        ? 'default'
                                        : 'outline'
                                "
                                as-child
                            >
                                <Link :href="show(payment.id)">
                                    {{
                                        payment.status.value === 'under_review'
                                            ? 'مراجعة'
                                            : 'عرض'
                                    }}
                                </Link>
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="payments.data.length === 0">
                        <td
                            colspan="7"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            لا توجد مدفوعات مطابقة.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav
            v-if="payments.meta.last_page > 1"
            class="flex items-center justify-between gap-3 text-sm"
            aria-label="التنقل بين الصفحات"
        >
            <p class="text-muted-foreground">
                {{ formatNumber(payments.meta.from ?? 0) }}–{{
                    formatNumber(payments.meta.to ?? 0)
                }}
                من {{ formatNumber(payments.meta.total) }}
            </p>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!payments.links.prev"
                    as-child
                >
                    <Link
                        :href="payments.links.prev ?? '#'"
                        preserve-scroll
                        :class="
                            !payments.links.prev &&
                            'pointer-events-none opacity-50'
                        "
                    >
                        <ChevronRight class="size-4" />
                        السابق
                    </Link>
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!payments.links.next"
                    as-child
                >
                    <Link
                        :href="payments.links.next ?? '#'"
                        preserve-scroll
                        :class="
                            !payments.links.next &&
                            'pointer-events-none opacity-50'
                        "
                    >
                        التالي
                        <ChevronLeft class="size-4" />
                    </Link>
                </Button>
            </div>
        </nav>
    </div>
</template>
