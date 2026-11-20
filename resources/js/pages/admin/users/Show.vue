<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { formatCurrency, formatDate } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { show as showStudy } from '@/routes/admin/studies';
import { index } from '@/routes/admin/users';
import type { AdminUserSummary, Study } from '@/types';

const props = defineProps<{
    user: { data: AdminUserSummary };
    studies: { data: Study[] };
}>();

const user = computed(() => props.user.data);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'المستخدمون', href: index() },
        ],
    },
});
</script>

<template>
    <Head :title="user.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="user.name" :description="user.email" />
            <Badge
                :variant="user.role.value === 'admin' ? 'default' : 'outline'"
                >{{ user.role.label }}</Badge
            >
        </div>

        <section class="rounded-2xl border bg-card p-5 text-sm">
            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-muted-foreground">تاريخ التسجيل</dt>
                    <dd class="font-medium">
                        {{ formatDate(user.created_at) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">
                        البريد الإلكتروني
                    </dt>
                    <dd class="font-medium">
                        {{ user.email_verified_at ? 'مؤكَّد' : 'غير مؤكَّد' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">عدد الدراسات</dt>
                    <dd class="font-medium tabular-nums">
                        {{ user.studies_count }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="overflow-x-auto rounded-2xl border bg-card">
            <h2 class="border-b p-4 font-semibold">الدراسات</h2>
            <table
                v-if="studies.data.length > 0"
                class="w-full min-w-[640px] text-sm"
            >
                <thead class="bg-muted/40 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">
                            الدراسة
                        </th>
                        <th class="px-4 py-3 text-start font-medium">الحالة</th>
                        <th class="px-4 py-3 text-start font-medium">الدفع</th>
                        <th class="px-4 py-3 text-start font-medium">
                            الإكمال
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="study in studies.data" :key="study.id">
                        <td class="px-4 py-3">
                            <Link
                                :href="showStudy(study.id)"
                                class="font-medium hover:text-primary hover:underline"
                                >{{ study.title }}</Link
                            >
                            <p class="text-xs text-muted-foreground">
                                {{ study.reference }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <StudyStatusBadge :status="study.status" />
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="study.payment"
                                class="flex flex-wrap items-center gap-2"
                            >
                                <PaymentStatusBadge
                                    :status="study.payment.status"
                                />
                                <span
                                    class="text-xs text-muted-foreground tabular-nums"
                                    >{{
                                        formatCurrency(
                                            study.payment.amount,
                                            study.payment.currency,
                                        )
                                    }}</span
                                >
                            </div>
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            {{ study.completion_percentage }}%
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="p-6 text-sm text-muted-foreground">
                لا توجد دراسات لهذا المستخدم.
            </p>
        </section>
    </div>
</template>
