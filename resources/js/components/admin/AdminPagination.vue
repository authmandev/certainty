<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { formatNumber } from '@/lib/format';
import type { Paginated } from '@/types';

defineProps<{
    page: Pick<Paginated<unknown>, 'links' | 'meta'>;
}>();
</script>

<template>
    <nav
        v-if="page.meta.last_page > 1"
        class="flex items-center justify-between gap-3 text-sm"
        aria-label="التنقل بين الصفحات"
    >
        <p class="text-muted-foreground">
            {{ formatNumber(page.meta.from ?? 0) }}–{{
                formatNumber(page.meta.to ?? 0)
            }}
            من {{ formatNumber(page.meta.total) }}
        </p>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" as-child>
                <Link
                    :href="page.links.prev ?? '#'"
                    preserve-scroll
                    :class="
                        !page.links.prev && 'pointer-events-none opacity-50'
                    "
                    :aria-disabled="!page.links.prev"
                >
                    <ChevronRight class="size-4" />
                    السابق
                </Link>
            </Button>
            <Button variant="outline" size="sm" as-child>
                <Link
                    :href="page.links.next ?? '#'"
                    preserve-scroll
                    :class="
                        !page.links.next && 'pointer-events-none opacity-50'
                    "
                    :aria-disabled="!page.links.next"
                >
                    التالي
                    <ChevronLeft class="size-4" />
                </Link>
            </Button>
        </div>
    </nav>
</template>
