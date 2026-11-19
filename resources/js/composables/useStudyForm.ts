import type { InertiaForm } from '@inertiajs/vue3';
import { inject, provide } from 'vue';
import type { InjectionKey } from 'vue';
import type { StudyFormData, StudyFormOptions } from '@/types';

export type StudyFormContext = {
    form: InertiaForm<StudyFormData>;
    options: StudyFormOptions;
    currency: () => string;
    feeAmount: string | null;
    error: (path: string) => string | undefined;
};

const studyFormKey: InjectionKey<StudyFormContext> = Symbol('studyForm');

/**
 * Shares the editor's form with the step components without prop drilling or prop mutation.
 */
export function provideStudyForm(context: StudyFormContext): void {
    provide(studyFormKey, context);
}

export function useStudyForm(): StudyFormContext {
    const context = inject(studyFormKey);

    if (!context) {
        throw new Error('useStudyForm() must be used inside the study editor.');
    }

    return context;
}

/**
 * A stable key for v-for, also persisted with each row.
 */
export function newRowKey(): string {
    return crypto.randomUUID();
}
