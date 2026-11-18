<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, Moon, Sun, X } from '@lucide/vue';
import { useWindowScroll } from '@vueuse/core';
import { ConfigProvider } from 'reka-ui';
import { computed, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, home, login, privacy, register, terms } from '@/routes';

const sections = [
    { id: 'features', title: 'المزايا' },
    { id: 'how-it-works', title: 'كيف تعمل' },
    { id: 'preview', title: 'معاينة المنصة' },
    { id: 'pricing', title: 'الأسعار' },
    { id: 'faq', title: 'الأسئلة الشائعة' },
] as const;

const { resolvedAppearance, updateAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');
const mobileMenuOpen = ref(false);
const { y } = useWindowScroll();
const isScrolled = computed(() => y.value > 8);

function toggleAppearance(): void {
    updateAppearance(isDark.value ? 'light' : 'dark');
}

function sectionHref(id: string): string {
    return `${home().url}#${id}`;
}
</script>

<template>
    <ConfigProvider dir="rtl">
        <div class="flex min-h-svh flex-col bg-background text-foreground">
            <header
                class="sticky top-0 z-50 transition-all duration-300"
                :class="
                    isScrolled || mobileMenuOpen
                        ? 'border-b border-border/60 bg-background/80 shadow-sm backdrop-blur-xl'
                        : 'border-b border-transparent'
                "
            >
                <div
                    class="mx-auto flex h-16 max-w-7xl items-center gap-8 px-4 sm:px-6"
                >
                    <Link :href="home()" class="flex items-center gap-2.5">
                        <AppLogoIcon class="size-8 fill-current text-primary" />
                        <span class="font-display text-lg font-bold">{{
                            $page.props.name
                        }}</span>
                    </Link>

                    <nav
                        class="hidden items-center gap-1 lg:flex"
                        aria-label="التنقل الرئيسي"
                    >
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="sectionHref(section.id)"
                            class="rounded-full px-3.5 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        >
                            {{ section.title }}
                        </a>
                    </nav>

                    <div class="ms-auto flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="rounded-full"
                            :aria-label="
                                isDark
                                    ? 'التبديل إلى الوضع الفاتح'
                                    : 'التبديل إلى الوضع الداكن'
                            "
                            @click="toggleAppearance"
                        >
                            <Sun v-if="isDark" class="size-5" />
                            <Moon v-else class="size-5" />
                        </Button>

                        <template v-if="$page.props.auth.user">
                            <Button
                                as-child
                                class="hidden rounded-full sm:inline-flex"
                            >
                                <Link :href="dashboard()">لوحة التحكم</Link>
                            </Button>
                        </template>
                        <template v-else>
                            <Button
                                variant="ghost"
                                as-child
                                class="hidden rounded-full sm:inline-flex"
                            >
                                <Link :href="login()">تسجيل الدخول</Link>
                            </Button>
                            <Button
                                as-child
                                class="hidden rounded-full shadow-md shadow-primary/20 sm:inline-flex"
                            >
                                <Link :href="register()">إنشاء حساب</Link>
                            </Button>
                        </template>

                        <Button
                            variant="ghost"
                            size="icon"
                            class="rounded-full lg:hidden"
                            :aria-label="
                                mobileMenuOpen ? 'إغلاق القائمة' : 'القائمة'
                            "
                            :aria-expanded="mobileMenuOpen"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <X v-if="mobileMenuOpen" class="size-5" />
                            <Menu v-else class="size-5" />
                        </Button>
                    </div>
                </div>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    leave-active-class="transition duration-150 ease-in"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <nav
                        v-if="mobileMenuOpen"
                        class="border-t border-border/60 px-4 pt-3 pb-5 lg:hidden"
                        aria-label="التنقل الرئيسي"
                    >
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="sectionHref(section.id)"
                            class="block rounded-xl px-3 py-2.5 text-sm hover:bg-accent"
                            @click="mobileMenuOpen = false"
                        >
                            {{ section.title }}
                        </a>
                        <div class="mt-3 flex gap-2 sm:hidden">
                            <template v-if="$page.props.auth.user">
                                <Button as-child class="flex-1">
                                    <Link :href="dashboard()">لوحة التحكم</Link>
                                </Button>
                            </template>
                            <template v-else>
                                <Button
                                    variant="outline"
                                    as-child
                                    class="flex-1"
                                >
                                    <Link :href="login()">تسجيل الدخول</Link>
                                </Button>
                                <Button as-child class="flex-1">
                                    <Link :href="register()">إنشاء حساب</Link>
                                </Button>
                            </template>
                        </div>
                    </nav>
                </Transition>
            </header>

            <main class="flex-1">
                <slot />
            </main>

            <footer class="border-t bg-muted/30">
                <div
                    class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.5fr_1fr_1fr]"
                >
                    <div>
                        <Link :href="home()" class="flex items-center gap-2.5">
                            <AppLogoIcon
                                class="size-8 fill-current text-primary"
                            />
                            <span class="font-display text-lg font-bold">{{
                                $page.props.name
                            }}</span>
                        </Link>
                        <p
                            class="mt-4 max-w-sm text-sm leading-relaxed text-muted-foreground"
                        >
                            منصة عربية لإعداد دراسات الجدوى المالية خطوة بخطوة،
                            من إدخال بيانات المشروع حتى التقرير النهائي.
                        </p>
                    </div>
                    <nav aria-label="المنصة">
                        <p class="text-sm font-semibold">المنصة</p>
                        <ul
                            class="mt-4 space-y-2.5 text-sm text-muted-foreground"
                        >
                            <li v-for="section in sections" :key="section.id">
                                <a
                                    :href="sectionHref(section.id)"
                                    class="transition-colors hover:text-foreground"
                                    >{{ section.title }}</a
                                >
                            </li>
                        </ul>
                    </nav>
                    <nav aria-label="روابط قانونية">
                        <p class="text-sm font-semibold">قانوني</p>
                        <ul
                            class="mt-4 space-y-2.5 text-sm text-muted-foreground"
                        >
                            <li>
                                <Link
                                    :href="privacy()"
                                    class="transition-colors hover:text-foreground"
                                    >سياسة الخصوصية</Link
                                >
                            </li>
                            <li>
                                <Link
                                    :href="terms()"
                                    class="transition-colors hover:text-foreground"
                                    >الشروط والأحكام</Link
                                >
                            </li>
                            <li>
                                <a
                                    :href="sectionHref('contact')"
                                    class="transition-colors hover:text-foreground"
                                    >تواصل معنا</a
                                >
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="border-t">
                    <p
                        class="mx-auto max-w-7xl px-4 py-6 text-sm text-muted-foreground sm:px-6"
                    >
                        © {{ new Date().getFullYear() }} {{ $page.props.name }}.
                        جميع الحقوق محفوظة.
                    </p>
                </div>
            </footer>
        </div>
    </ConfigProvider>
</template>
