import type { InertiaForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { useDebounceFn, useEventListener } from '@vueuse/core';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import type { ComputedRef, Ref } from 'vue';

export type SaveStatus = 'saved' | 'dirty' | 'saving' | 'error';

type Options = {
    url: () => string;
    delay?: number;
    only?: string[];
};

/**
 * Saves a draft form a moment after the customer stops typing. Requests never
 * overlap: a change made while saving is sent as soon as the request ends.
 * Leaving the page with unsaved changes asks for confirmation.
 */
export function useAutosave<T extends object>(
    form: InertiaForm<T>,
    { url, delay = 1200, only = [] }: Options,
): {
    status: ComputedRef<SaveStatus>;
    lastSavedAt: Ref<Date | null>;
    saveNow: () => void;
} {
    const serialize = (): string => JSON.stringify(form.data());

    const lastSavedSnapshot = ref(serialize());
    const lastSavedAt = ref<Date | null>(null);
    const failed = ref(false);
    let queued = false;

    const isDirty = computed(() => serialize() !== lastSavedSnapshot.value);

    const status = computed<SaveStatus>(() => {
        if (form.processing) {
            return 'saving';
        }

        if (failed.value) {
            return 'error';
        }

        return isDirty.value ? 'dirty' : 'saved';
    });

    function saveNow(): void {
        if (form.processing) {
            queued = true;

            return;
        }

        if (!isDirty.value) {
            return;
        }

        const snapshot = serialize();

        form.transform((data) => emptyStringsToNull(data) as T).put(url(), {
            preserveScroll: true,
            preserveState: true,
            only,
            onSuccess: () => {
                lastSavedSnapshot.value = snapshot;
                lastSavedAt.value = new Date();
                failed.value = false;
                form.clearErrors();
            },
            onError: () => {
                failed.value = true;
            },
            onFinish: () => {
                if (queued) {
                    queued = false;
                    saveNow();
                }
            },
        });
    }

    const debouncedSave = useDebounceFn(saveNow, delay);

    watch(serialize, () => {
        failed.value = false;
        void debouncedSave();
    });

    useEventListener(window, 'beforeunload', (event: BeforeUnloadEvent) => {
        if (isDirty.value || form.processing) {
            event.preventDefault();
        }
    });

    const removeNavigationGuard = router.on('before', (event) => {
        const visit = event.detail.visit;

        if (visit.method !== 'get' || !isDirty.value) {
            return;
        }

        return window.confirm(
            'لديك تغييرات لم تُحفظ بعد. هل تريد المغادرة دون حفظها؟',
        );
    });

    onBeforeUnmount(removeNavigationGuard);

    return { status, lastSavedAt, saveNow };
}

/**
 * Cleared inputs produce empty strings; the API stores them as null.
 */
function emptyStringsToNull(value: unknown): unknown {
    if (value === '') {
        return null;
    }

    if (Array.isArray(value)) {
        return value.map(emptyStringsToNull);
    }

    if (value !== null && typeof value === 'object') {
        return Object.fromEntries(
            Object.entries(value).map(([key, entry]) => [
                key,
                emptyStringsToNull(entry),
            ]),
        );
    }

    return value;
}
