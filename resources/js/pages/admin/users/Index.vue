<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { ref, watch } from 'vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import FilterTabs from '@/components/admin/FilterTabs.vue';
import SearchInput from '@/components/admin/SearchInput.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { formatDate, formatNumber } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index, show } from '@/routes/admin/users';
import type { AdminUserSummary, OptionItem, Paginated } from '@/types';

type Role = AdminUserSummary['role']['value'];

const props = defineProps<{
    users: Paginated<AdminUserSummary>;
    filters: { role: Role | null; search: string | null };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'المستخدمون', href: index() },
        ],
    },
});

const roles: OptionItem<Role>[] = [
    { value: 'customer', label: 'العملاء' },
    { value: 'admin', label: 'المديرون' },
];

const role = ref(props.filters.role);
const search = ref(props.filters.search ?? '');

function reload(): void {
    router.get(
        index.url({
            query: {
                role: role.value ?? undefined,
                search: search.value.trim() || undefined,
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(role, reload);
watchDebounced(search, reload, { debounce: 350 });
</script>

<template>
    <Head title="المستخدمون" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading title="المستخدمون" description="حسابات العملاء والمديرين" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <FilterTabs v-model="role" :options="roles" label="نوع الحساب" />
            <SearchInput
                v-model="search"
                placeholder="الاسم أو البريد الإلكتروني"
            />
        </div>

        <div class="overflow-x-auto rounded-2xl border bg-card">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="border-b bg-muted/40 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">
                            المستخدم
                        </th>
                        <th class="px-4 py-3 text-start font-medium">النوع</th>
                        <th class="px-4 py-3 text-start font-medium">
                            الدراسات
                        </th>
                        <th class="px-4 py-3 text-start font-medium">
                            تاريخ التسجيل
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="transition-colors hover:bg-muted/40"
                    >
                        <td class="px-4 py-3">
                            <Link
                                :href="show(user.id)"
                                class="font-medium hover:text-primary hover:underline"
                                >{{ user.name }}</Link
                            >
                            <p class="text-xs text-muted-foreground">
                                {{ user.email }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    user.role.value === 'admin'
                                        ? 'default'
                                        : 'outline'
                                "
                                >{{ user.role.label }}</Badge
                            >
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            {{ formatNumber(user.studies_count) }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ formatDate(user.created_at) }}
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            لا يوجد مستخدمون مطابقون.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AdminPagination :page="users" />
    </div>
</template>
