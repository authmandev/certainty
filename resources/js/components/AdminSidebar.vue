<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, ShieldCheck } from '@lucide/vue';
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
import { dashboard } from '@/routes/admin';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'نظرة عامة',
        href: dashboard(),
        icon: LayoutDashboard,
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
