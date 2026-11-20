<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatDate } from '@/lib/format';

/**
 * The days-and-reason form shared by "extend draft" and "reopen study".
 */
const { url, defaultDays, baseDate } = defineProps<{
    url: string;
    title: string;
    description: string;
    daysLabel: string;
    submitLabel: string;
    defaultDays: number;
    baseDate: Date;
}>();

const open = defineModel<boolean>('open', { required: true });

const form = useForm({ days: defaultDays, reason: '' });

const newDeadline = computed(() => {
    const days = Number(form.days);

    if (!Number.isFinite(days) || days < 1) {
        return null;
    }

    const date = new Date(baseDate);
    date.setDate(date.getDate() + days);

    return date;
});

function submit(): void {
    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>
                <div class="grid gap-2">
                    <Label for="days">{{ daysLabel }}</Label>
                    <Input
                        id="days"
                        v-model="form.days"
                        type="number"
                        min="1"
                        max="365"
                        inputmode="numeric"
                        required
                    />
                    <p
                        v-if="newDeadline"
                        class="text-xs text-muted-foreground"
                        data-test="new-deadline"
                    >
                        الموعد الجديد:
                        {{ formatDate(newDeadline.toISOString()) }}
                    </p>
                    <InputError :message="form.errors.days" />
                </div>
                <div class="grid gap-2">
                    <Label for="reason">السبب</Label>
                    <Textarea
                        id="reason"
                        v-model="form.reason"
                        rows="3"
                        maxlength="1000"
                        required
                    />
                    <InputError :message="form.errors.reason" />
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline">إلغاء</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        <LoaderCircle
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />
                        {{ submitLabel }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
