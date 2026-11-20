<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    CircleCheck,
    CircleDashed,
    FileCheck,
    LoaderCircle,
} from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { finalize } from '@/routes/studies';
import type { Finalization } from '@/types';

const { studyId, finalization } = defineProps<{
    studyId: number;
    finalization: Finalization;
}>();

const open = ref(false);
const form = useForm({ confirmed: false });

function submit(): void {
    form.post(finalize(studyId).url, {
        preserveScroll: true,
        onSuccess: () => (open.value = false),
    });
}
</script>

<template>
    <section class="rounded-2xl border bg-card p-6 text-sm">
        <div class="flex items-center gap-2.5">
            <span
                class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
            >
                <FileCheck class="size-4" />
            </span>
            <h2 class="font-semibold">إنهاء الدراسة والتقرير النهائي</h2>
        </div>
        <p class="mt-3 leading-relaxed text-muted-foreground">
            بعد إنهاء الدراسة تُحفظ بياناتها ونتائجها كما هي، ويصدر التقرير
            النهائي بصيغة PDF، ولا يمكن تعديلها بعد ذلك.
        </p>

        <ul class="mt-4 space-y-2.5" data-test="finalization-checks">
            <li
                v-for="check in finalization.checks"
                :key="check.key"
                class="flex items-start gap-2.5"
            >
                <CircleCheck
                    v-if="check.passed"
                    class="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                />
                <CircleDashed
                    v-else
                    class="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400"
                />
                <div>
                    <p :class="check.passed ? '' : 'font-medium'">
                        {{ check.label }}
                    </p>
                    <p
                        v-if="!check.passed && check.detail"
                        class="text-xs text-muted-foreground"
                    >
                        {{ check.detail }}
                    </p>
                </div>
            </li>
        </ul>

        <InputError
            class="mt-3"
            :message="(form.errors as Record<string, string>).finalize"
        />

        <Button
            class="mt-5 w-full"
            :disabled="!finalization.ready"
            @click="open = true"
        >
            إنهاء الدراسة وإصدار التقرير النهائي
        </Button>

        <Dialog v-model:open="open">
            <DialogContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <DialogHeader>
                        <DialogTitle>إنهاء الدراسة؟</DialogTitle>
                        <DialogDescription>
                            سيُصدر التقرير النهائي من البيانات الحالية، ولن
                            تتمكن من تعديل الدراسة بعد الإنهاء.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex items-start gap-3">
                        <Checkbox
                            id="confirmed"
                            v-model="form.confirmed"
                            class="mt-0.5"
                        />
                        <Label
                            for="confirmed"
                            class="leading-relaxed font-normal"
                        >
                            أؤكد أن بيانات الدراسة صحيحة ونهائية.
                        </Label>
                    </div>
                    <InputError :message="form.errors.confirmed" />
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button type="button" variant="outline"
                                >إلغاء</Button
                            >
                        </DialogClose>
                        <Button
                            type="submit"
                            :disabled="!form.confirmed || form.processing"
                            data-test="confirm-finalize"
                        >
                            <LoaderCircle
                                v-if="form.processing"
                                class="size-4 animate-spin"
                            />
                            إنهاء وإصدار التقرير
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </section>
</template>
