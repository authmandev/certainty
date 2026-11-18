<script setup lang="ts">
import { Mail, MessageCircleQuestion, Phone, Plus } from '@lucide/vue';
import Reveal from '@/components/landing/Reveal.vue';
import SectionHeading from '@/components/landing/SectionHeading.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';

defineProps<{
    faqs: { question: string; answer: string }[];
    contact: { email: string | null; phone: string | null };
}>();
</script>

<template>
    <section id="faq" class="relative scroll-mt-20 py-24 sm:py-32">
        <div
            class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_1.6fr]"
        >
            <div class="lg:sticky lg:top-28 lg:self-start">
                <SectionHeading
                    align="start"
                    eyebrow="الأسئلة الشائعة"
                    title="إجابات على أكثر ما يُسأل عنه"
                    description="كل ما تحتاج معرفته عن إعداد الدراسة والدفع والتقرير النهائي."
                />

                <Reveal
                    id="contact"
                    :delay="100"
                    class="mt-8 scroll-mt-28 rounded-3xl border bg-card p-6"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <MessageCircleQuestion class="size-5" />
                    </span>
                    <p class="mt-4 font-display text-lg font-bold">
                        لم تجد إجابتك؟
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        تواصل معنا وسنساعدك في كل ما يخص دراستك أو الدفع.
                    </p>
                    <div
                        v-if="contact.email || contact.phone"
                        class="mt-5 flex flex-col gap-2"
                    >
                        <a
                            v-if="contact.email"
                            :href="`mailto:${contact.email}`"
                            class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm transition-colors hover:bg-accent"
                        >
                            <Mail class="size-4 text-primary" />
                            <span dir="ltr">{{ contact.email }}</span>
                        </a>
                        <a
                            v-if="contact.phone"
                            :href="`tel:${contact.phone}`"
                            class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm transition-colors hover:bg-accent"
                        >
                            <Phone class="size-4 text-primary" />
                            <span dir="ltr">{{ contact.phone }}</span>
                        </a>
                    </div>
                </Reveal>
            </div>

            <div class="space-y-3">
                <Reveal
                    v-for="(faq, index) in faqs"
                    :key="faq.question"
                    :delay="index * 60"
                >
                    <Collapsible
                        v-slot="{ open }"
                        class="rounded-2xl border bg-card transition-colors data-[state=open]:border-primary/40 data-[state=open]:shadow-lg data-[state=open]:shadow-primary/5"
                    >
                        <CollapsibleTrigger
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start font-medium"
                        >
                            {{ faq.question }}
                            <span
                                class="flex size-7 shrink-0 items-center justify-center rounded-full border transition-all duration-300"
                                :class="
                                    open
                                        ? 'rotate-45 border-primary bg-primary text-primary-foreground'
                                        : ''
                                "
                            >
                                <Plus class="size-4" />
                            </span>
                        </CollapsibleTrigger>
                        <CollapsibleContent
                            class="overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down"
                        >
                            <p
                                class="px-6 pb-5 leading-relaxed text-muted-foreground"
                            >
                                {{ faq.answer }}
                            </p>
                        </CollapsibleContent>
                    </Collapsible>
                </Reveal>
            </div>
        </div>
    </section>
</template>
