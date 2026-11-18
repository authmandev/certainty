import { useIntersectionObserver } from '@vueuse/core';
import type { MaybeElementRef } from '@vueuse/core';
import { ref } from 'vue';
import type { Ref } from 'vue';

/**
 * Becomes true the first time the element scrolls into view and stays true,
 * so entrance animations run once and never loop.
 */
export function useInView(
    target: MaybeElementRef,
    options: { threshold?: number; rootMargin?: string } = {},
): Ref<boolean> {
    const inView = ref(false);

    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
        inView.value = true;

        return inView;
    }

    const { stop } = useIntersectionObserver(
        target,
        ([entry]) => {
            if (entry?.isIntersecting) {
                inView.value = true;
                stop();
            }
        },
        {
            threshold: options.threshold ?? 0.15,
            rootMargin: options.rootMargin ?? '0px 0px -10% 0px',
        },
    );

    return inView;
}
