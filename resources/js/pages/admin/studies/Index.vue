<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { ref, watch } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import SearchInput from '@/components/admin/SearchInput.vue';
import Heading from '@/components/Heading.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index, show } from '@/routes/admin/studies';
import { show as showUser } from '@/routes/admin/users';
import type {
    OptionItem,
    Paginated,
    PaymentStatus,
    Study,
    StudyStatus,
} from '@/types';

type AdminStudy = Study & {
    customer?: { id: number; name: string; email: string };
};

const props = defineProps<{
    studies: Paginated<AdminStudy>;
    filters: {
        status: StudyStatus | null;
        payment: PaymentStatus | null;
        search: string | null;
    };
    statuses: OptionItem<StudyStatus>[];
    paymentStatuses: OptionItem<PaymentStatus>[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'الدراسات', href: index() },
        ],
    },
});

const status = ref(props.filters.status);
const payment = ref(props.filters.payment);
const search = ref(props.filters.search ?? '');

function reload(): void {
    router.get(
        index.url({
            query: {
                status: status.value ?? undefined,
                payment: payment.value ?? undefined,
                search: search.value.trim() || undefined,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch([status, payment], reload);
watchDebounced(search, reload, { debounce: 350 });
</script>

<template>
    <Head title="الدراسات" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="الدراسات"
            description="جميع دراسات العملاء مع حالتها وحالة الدفع"
        />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <FilterTabs
                    v-model="status"
                    :options="statuses"
                    label="حالة الدراسة"
                    all-label="كل الحالات"
                />
                <FilterTabs
                    v-model="payment"
                    :options="paymentStatuses"
                    label="حالة الدفع"
                    all-label="كل المدفوعات"
                />
            </div>
            <SearchInput
                v-model="search"
                placeholder="اسم الدراسة أو المرجع أو العميل"
            />
        </div>

        <div class="overflow-x-auto rounded-2xl border bg-card">
            <table class="w-full min-w-[820px] text-sm">
                <thead class="border-b bg-muted/40 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">
                            الدراسة
                        </th>
                        <th class="px-4 py-3 text-start font-medium">العميل</th>
                        <th class="px-4 py-3 text-start font-medium">الحالة</th>
                        <th class="px-4 py-3 text-start font-medium">الدفع</th>
                        <th class="px-4 py-3 text-start font-medium">
                            الإكمال
                        </th>
                        <th class="px-4 py-3 text-start font-medium">
                            الموعد النهائي
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="study in studies.data"
                        :key="study.id"
                        class="transition-colors hover:bg-muted/40"
                    >
                        <td class="px-4 py-3">
                            <Link
                                :href="show(study.id)"
                                class="font-medium hover:text-primary hover:underline"
                                >{{ study.title }}</Link
                            >
                            <p class="text-xs text-muted-foreground">
                                {{ study.reference }} · {{ study.project_name }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <Link
                                v-if="study.customer"
                                :href="showUser(study.customer.id)"
                                class="hover:underline"
                                >{{ study.customer.name }}</Link
                            >
                            <p class="text-xs text-muted-foreground">
                                {{ study.customer?.email }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <StudyStatusBadge :status="study.status" />
                        </td>
                        <td class="px-4 py-3">
                            <PaymentStatusBadge
                                v-if="study.payment"
                                :status="study.payment.status"
                            />
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            {{ study.completion_percentage }}%
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ formatDate(study.expires_at) }}
                        </td>
                    </tr>
                    <tr v-if="studies.data.length === 0">
                        <td
                            colspan="6"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            لا توجد دراسات مطابقة.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AdminPagination :page="studies" />
    </div>
</template>
