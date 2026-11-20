<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { ref, watch } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import AuditTimeline from '@/components/admin/AuditTimeline.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import SearchInput from '@/components/admin/SearchInput.vue';
import Heading from '@/components/Heading.vue';
import { auditLog, dashboard } from '@/routes/admin';
import type {
    AuditActionValue,
    AuditLogEntry,
    OptionItem,
    Paginated,
} from '@/types';

const props = defineProps<{
    logs: Paginated<AuditLogEntry>;
    filters: { action: AuditActionValue | null; search: string | null };
    actions: OptionItem<AuditActionValue>[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'سجل العمليات', href: auditLog() },
        ],
    },
});

const action = ref(props.filters.action);
const search = ref(props.filters.search ?? '');

function reload(): void {
    router.get(
        auditLog.url({
            query: {
                action: action.value ?? undefined,
                search: search.value.trim() || undefined,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(action, reload);
watchDebounced(search, reload, { debounce: 350 });
</script>

<template>
    <Head title="سجل العمليات" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="سجل العمليات"
            description="العمليات الحساسة على الدراسات والمدفوعات، من الأحدث إلى الأقدم"
        />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <FilterTabs
                v-model="action"
                :options="actions"
                label="نوع العملية"
            />
            <SearchInput
                v-model="search"
                placeholder="مرجع الدراسة أو المنفّذ"
            />
        </div>

        <section class="rounded-2xl border bg-card p-5">
            <AuditTimeline :entries="logs.data" show-study />
        </section>

        <AdminPagination :page="logs" />
    </div>
</template>
