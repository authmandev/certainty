<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 32 32"
        :class="className"
        v-bind="$attrs"
        aria-hidden="true"
    >
        <path
            d="M6 3h20a3 3 0 0 1 3 3v20a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3Zm2.5 18.5a1.5 1.5 0 0 0 2.12 2.12l5.38-5.38 3.38 3.38a1.5 1.5 0 0 0 2.12 0l5-5a1.5 1.5 0 1 0-2.12-2.12L20.44 18.44l-3.38-3.38a1.5 1.5 0 0 0-2.12 0L8.5 21.5ZM9 8.5a1.5 1.5 0 0 0 0 3h6a1.5 1.5 0 0 0 0-3H9Z"
            fill-rule="evenodd"
        />
    </svg>
</template>
