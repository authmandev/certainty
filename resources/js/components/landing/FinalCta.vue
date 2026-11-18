<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Reveal from '@/components/landing/Reveal.vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
import { create as createStudy } from '@/routes/studies';
</script>

<template>
    <section class="px-4 pb-24 sm:px-6 sm:pb-32">
        <Reveal
            class="relative mx-auto max-w-6xl overflow-hidden rounded-[2.5rem] bg-ink px-6 py-16 text-center text-white sm:px-12 sm:py-20"
        >
            <div class="absolute inset-0" aria-hidden="true">
                <div
                    class="bg-grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)] opacity-25 [--foreground:white]"
                ></div>
                <div
                    class="absolute start-1/4 -top-32 size-96 animate-aurora rounded-full bg-primary/40 blur-3xl"
                ></div>
                <div
                    class="absolute end-1/4 -bottom-40 size-96 animate-aurora rounded-full bg-gold/25 blur-3xl [animation-delay:-8s]"
                ></div>
            </div>

            <div class="relative">
                <AppLogoIcon class="mx-auto size-12 fill-current text-gold" />
                <h2
                    class="mx-auto mt-6 max-w-2xl font-display text-3xl leading-[1.3] font-bold text-balance sm:text-5xl"
                >
                    جاهز لاختبار جدوى فكرتك بالأرقام؟
                </h2>
                <p class="mx-auto mt-5 max-w-xl text-lg text-white/70">
                    ابدأ دراستك الآن مجانًا، وادفع فقط عندما تكون جاهزًا لإصدار
                    التقرير النهائي.
                </p>
                <div
                    class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row"
                >
                    <Button
                        size="lg"
                        class="group h-12 w-full bg-white px-8 text-base text-ink hover:bg-white/90 sm:w-auto"
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
                        variant="ghost"
                        class="h-12 w-full px-8 text-base text-white ring-1 ring-white/25 hover:bg-white/10 hover:text-white sm:w-auto"
                        as-child
                    >
                        <Link :href="register()">إنشاء حساب</Link>
                    </Button>
                </div>
            </div>
        </Reveal>
    </section>
</template>
