<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarRange, FileCheck2, Wallet } from '@lucide/vue';
import HeroDashboard from '@/components/landing/HeroDashboard.vue';
import { Button } from '@/components/ui/button';
import { create as createStudy } from '@/routes/studies';

const highlights = [
    { icon: CalendarRange, text: 'توقعات مالية حتى 15 سنة' },
    { icon: FileCheck2, text: 'قائمة الدخل والتدفقات والميزانية' },
    { icon: Wallet, text: 'ابدأ مجانًا وادفع قبل إصدار التقرير' },
];
</script>

<template>
    <section class="relative isolate overflow-hidden">
        <!-- Layered background: grid, aurora glows and a soft fade -->
        <div class="absolute inset-0 -z-10" aria-hidden="true">
            <div
                class="bg-grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]"
            ></div>
            <div
                class="absolute start-[-10%] -top-40 size-[38rem] animate-aurora rounded-full bg-primary/25 blur-3xl dark:bg-primary/20"
            ></div>
            <div
                class="absolute end-[-15%] top-20 size-[30rem] animate-aurora rounded-full bg-gold/20 blur-3xl [animation-delay:-6s] dark:bg-gold/10"
            ></div>
            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-background"
            ></div>
        </div>

        <div
            class="mx-auto grid max-w-7xl items-center gap-16 px-4 pt-14 pb-24 sm:px-6 lg:grid-cols-[1fr_1.1fr] lg:gap-10 lg:pt-20 lg:pb-32"
        >
            <div
                class="animate-in text-center duration-700 fade-in slide-in-from-bottom-4 lg:text-start"
            >
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-background/70 px-3 py-1.5 text-sm font-medium text-primary shadow-sm backdrop-blur"
                >
                    <span class="relative flex size-2">
                        <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-primary opacity-60 motion-reduce:hidden"
                        ></span>
                        <span
                            class="relative inline-flex size-2 rounded-full bg-primary"
                        ></span>
                    </span>
                    منصة عربية لدراسات الجدوى المالية
                </span>

                <h1
                    class="mt-6 font-display text-4xl leading-[1.25] font-bold tracking-tight text-balance sm:text-5xl xl:text-6xl"
                >
                    حوّل فكرة مشروعك إلى
                    <span
                        class="bg-gradient-to-l from-primary via-teal-500 to-gold bg-clip-text text-transparent"
                        >قرار مبني على أرقام</span
                    >
                </h1>

                <p
                    class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-pretty text-muted-foreground lg:mx-0"
                >
                    أدخل بيانات مشروعك خطوة بخطوة، وشاهد قائمة الدخل والتدفقات
                    النقدية والميزانية تتشكل أمامك، ثم احصل على تقرير جدوى
                    احترافي جاهز للمشاركة مع الشركاء والممولين.
                </p>

                <div
                    class="mt-9 flex flex-col items-center gap-3 sm:flex-row sm:justify-center lg:justify-start"
                >
                    <Button
                        size="lg"
                        class="group h-12 w-full px-7 text-base shadow-lg shadow-primary/25 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary/30 sm:w-auto"
                        as-child
                    >
                        <Link :href="createStudy()">
                            ابدأ دراسة الجدوى
                            <ArrowLeft
                                class="size-4 transition-transform group-hover:-translate-x-1"
                            />
                        </Link>
                    </Button>
                    <Button
                        size="lg"
                        variant="outline"
                        class="h-12 w-full bg-background/60 px-7 text-base backdrop-blur sm:w-auto"
                        as-child
                    >
                        <a href="#how-it-works">شاهد كيف تعمل</a>
                    </Button>
                </div>

                <ul
                    class="mt-10 flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm text-muted-foreground lg:justify-start"
                >
                    <li
                        v-for="item in highlights"
                        :key="item.text"
                        class="flex items-center gap-2"
                    >
                        <component
                            :is="item.icon"
                            class="size-4 text-primary"
                        />
                        {{ item.text }}
                    </li>
                </ul>
            </div>

            <div
                class="animate-in px-2 delay-200 duration-1000 fill-mode-both fade-in slide-in-from-bottom-8 sm:px-8 lg:px-0"
            >
                <HeroDashboard />
            </div>
        </div>
    </section>
</template>
