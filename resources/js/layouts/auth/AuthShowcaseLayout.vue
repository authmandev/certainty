<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    FileText,
    Moon,
    ShieldCheck,
    Sun,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import HeroDashboard from '@/components/landing/HeroDashboard.vue';
import { useAppearance } from '@/composables/useAppearance';
import { home, privacy, terms } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const { resolvedAppearance, updateAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

const highlights = [
    { icon: BarChart3, text: 'ملخص مالي مباشر أثناء إدخال البيانات' },
    { icon: ShieldCheck, text: 'بياناتك وإيصالاتك محمية ولا يطّلع عليها غيرك' },
    { icon: FileText, text: 'تقرير PDF احترافي بعد اعتماد الدراسة' },
];
</script>

<template>
    <div
        class="grid min-h-svh bg-background lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]"
    >
        <!-- Form side -->
        <div class="relative isolate flex flex-col overflow-hidden">
            <div class="absolute inset-0 -z-10" aria-hidden="true">
                <div
                    class="bg-grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_at_top,black_20%,transparent_70%)]"
                ></div>
                <div
                    class="absolute -start-32 -top-40 size-96 animate-aurora rounded-full bg-primary/15 blur-3xl"
                ></div>
                <div
                    class="absolute -end-32 -bottom-40 size-80 animate-aurora rounded-full bg-gold/15 blur-3xl [animation-delay:-7s] lg:hidden"
                ></div>
            </div>

            <header
                class="flex items-center justify-between px-6 py-5 sm:px-10"
            >
                <Link :href="home()" class="flex items-center gap-2.5">
                    <AppLogoIcon class="size-8 fill-current text-primary" />
                    <span class="font-display text-lg font-bold">{{
                        $page.props.name
                    }}</span>
                </Link>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        :aria-label="
                            isDark
                                ? 'التبديل إلى الوضع الفاتح'
                                : 'التبديل إلى الوضع الداكن'
                        "
                        @click="updateAppearance(isDark ? 'light' : 'dark')"
                    >
                        <Sun v-if="isDark" class="size-4" />
                        <Moon v-else class="size-4" />
                    </button>
                    <Link
                        :href="home()"
                        class="group flex items-center gap-1.5 rounded-full px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                        <ArrowRight
                            class="size-4 transition-transform group-hover:translate-x-0.5"
                        />
                        الرئيسية
                    </Link>
                </div>
            </header>

            <main
                class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10"
            >
                <div
                    class="w-full max-w-md animate-in duration-700 fade-in slide-in-from-bottom-4"
                >
                    <div class="mb-8">
                        <h1
                            class="font-display text-3xl leading-tight font-bold tracking-tight text-balance"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="mt-3 leading-relaxed text-muted-foreground"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border bg-card/80 p-6 shadow-[0_20px_60px_-25px_rgb(15_40_60/0.25)] backdrop-blur-xl sm:p-8"
                    >
                        <slot />
                    </div>
                </div>
            </main>

            <footer
                class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 text-xs text-muted-foreground sm:px-10"
            >
                <span
                    >© {{ new Date().getFullYear() }}
                    {{ $page.props.name }}</span
                >
                <nav class="flex gap-4" aria-label="روابط قانونية">
                    <Link :href="privacy()" class="hover:text-foreground"
                        >سياسة الخصوصية</Link
                    >
                    <Link :href="terms()" class="hover:text-foreground"
                        >الشروط والأحكام</Link
                    >
                </nav>
            </footer>
        </div>

        <!-- Showcase side -->
        <aside
            class="relative isolate hidden overflow-hidden bg-ink p-10 text-white lg:flex lg:flex-col xl:p-14"
            aria-label="عن المنصة"
        >
            <div class="absolute inset-0 -z-10" aria-hidden="true">
                <div
                    class="bg-grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_80%)] opacity-25 [--foreground:white]"
                ></div>
                <div
                    class="absolute -end-24 -top-32 size-[28rem] animate-aurora rounded-full bg-primary/45 blur-3xl"
                ></div>
                <div
                    class="absolute -start-24 -bottom-40 size-[26rem] animate-aurora rounded-full bg-gold/25 blur-3xl [animation-delay:-9s]"
                ></div>
            </div>

            <div class="max-w-lg">
                <p
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-medium text-white/80 ring-1 ring-white/15"
                >
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    منصة عربية لدراسات الجدوى المالية
                </p>
                <h2
                    class="mt-6 font-display text-3xl leading-[1.35] font-bold text-balance xl:text-4xl"
                >
                    قرارات مشروعك تستحق أرقامًا
                    <span class="text-gold">واضحة وموثوقة</span>
                </h2>
            </div>

            <div class="relative my-auto py-14 [perspective:1600px]">
                <div
                    class="origin-center scale-[0.92] [transform:rotateY(-8deg)_rotateX(4deg)] xl:scale-100"
                >
                    <HeroDashboard />
                </div>
            </div>

            <ul class="grid gap-3 text-sm text-white/75 xl:grid-cols-3">
                <li
                    v-for="item in highlights"
                    :key="item.text"
                    class="flex items-start gap-2.5 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10"
                >
                    <component
                        :is="item.icon"
                        class="mt-0.5 size-4 shrink-0 text-gold"
                    />
                    {{ item.text }}
                </li>
            </ul>
        </aside>
    </div>
</template>
