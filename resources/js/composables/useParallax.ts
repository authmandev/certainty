import {
    useMediaQuery,
    usePreferredReducedMotion,
    useWindowScroll,
} from '@vueuse/core';
import { computed } from 'vue';
import type { ComputedRef, CSSProperties } from 'vue';

/**
 * Scroll-linked vertical offset for decorative layers. It only runs on wide
 * screens and is disabled when the visitor prefers reduced motion.
 */
export function useParallax(
    factor: number,
    maxOffset = 80,
): ComputedRef<CSSProperties> {
    const { y } = useWindowScroll();
    const reducedMotion = usePreferredReducedMotion();
    const isWide = useMediaQuery('(min-width: 1024px)');

    return computed(() => {
        if (reducedMotion.value === 'reduce' || !isWide.value) {
            return {};
        }

        const offset = Math.max(
            Math.min(y.value * factor, maxOffset),
            -maxOffset,
        );

        return { transform: `translate3d(0, ${offset}px, 0)` };
    });
}
