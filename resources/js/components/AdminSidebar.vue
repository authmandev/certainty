<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Banknote,
    FileText,
    History,
    LayoutDashboard,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { auditLog, dashboard } from '@/routes/admin';
import { index as payments } from '@/routes/admin/payments';
import { index as studies } from '@/routes/admin/studies';
import { index as users } from '@/routes/admin/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'نظرة عامة',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'المستخدمون',
        href: users(),
        icon: Users,
    },
    {
        title: 'الدراسات',
        href: studies(),
        icon: FileText,
    },
    {
        title: 'المدفوعات',
        href: payments(),
        icon: Banknote,
    },
    {
        title: 'سجل العمليات',
        href: auditLog(),
        icon: History,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <div
                                class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
                            >
                                <ShieldCheck class="size-5" />
                            </div>
                            <div class="ms-1 grid flex-1 text-start text-sm">
                                <span
                                    class="truncate leading-tight font-semibold"
                                    >لوحة الإدارة</span
                                >
                                <span
                                    class="truncate text-xs text-muted-foreground"
                                    >{{ $page.props.name }}</span
                                >
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="الإدارة" :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
