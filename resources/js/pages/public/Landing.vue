<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardShowcase from '@/components/landing/DashboardShowcase.vue';
import FaqSection from '@/components/landing/FaqSection.vue';
import FeatureBento from '@/components/landing/FeatureBento.vue';
import FinalCta from '@/components/landing/FinalCta.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import PricingSection from '@/components/landing/PricingSection.vue';
import ProcessTimeline from '@/components/landing/ProcessTimeline.vue';
import ReportPreview from '@/components/landing/ReportPreview.vue';
import { formatCurrency } from '@/lib/format';

const props = defineProps<{
    price: number;
    currency: string;
    draftDurationDays: number;
    contact: {
        email: string | null;
        phone: string | null;
    };
}>();

const formattedPrice = computed(() =>
    formatCurrency(props.price, props.currency),
);

const faqs = computed(() => [
    {
        question: 'هل أحتاج إلى الدفع قبل البدء؟',
        answer: 'لا. يمكنك إنشاء الدراسة وإدخال جميع بياناتك ومشاهدة الملخص المالي قبل اعتماد الدفع. يلزم اعتماد الدفع فقط لإنهاء الدراسة وإصدار التقرير النهائي.',
    },
    {
        question: 'كيف أدفع رسوم الدراسة؟',
        answer: `الدفع بالتحويل البنكي فقط. تظهر لك بيانات الحساب البنكي ورقم مرجع الدفع في صفحة الدراسة، وبعد التحويل ترفع صورة الإيصال (JPG أو PNG أو PDF) ليراجعها فريقنا. رسوم الدراسة الواحدة ${formattedPrice.value}.`,
    },
    {
        question: 'كم من الوقت لدي لإكمال الدراسة؟',
        answer: `${props.draftDurationDays} يومًا من تاريخ إنشاء الدراسة. بعد انتهاء المدة تُقفل الدراسة للتعديل مع الاحتفاظ ببياناتك، ويمكنك التواصل معنا لتمديدها.`,
    },
    {
        question: 'هل يمكنني إنشاء أكثر من دراسة؟',
        answer: 'نعم. كل دراسة مستقلة ببياناتها ودفعتها وتقريرها، ورسومها تُحسب لكل دراسة على حدة.',
    },
    {
        question: 'هل يمكنني تعديل الدراسة بعد إصدار التقرير؟',
        answer: 'تصبح الدراسة للقراءة فقط بعد إنهائها. إذا واجهت مشكلة تقنية يمكن لفريقنا إعادة فتحها، ويصدر تقرير بنسخة جديدة مع الاحتفاظ بالنسخة السابقة.',
    },
    {
        question: 'هل تُعد الدراسة رأيًا محاسبيًا معتمدًا؟',
        answer: 'لا. الدراسة أداة لتقدير الجدوى المالية بناءً على البيانات التي تدخلها، ولا تمثل شهادة محاسبية أو استشارة استثمارية معتمدة.',
    },
    {
        question: 'هل بياناتي محمية؟',
        answer: 'نعم. لا يطّلع على دراستك وإيصالاتك وتقاريرك إلا أنت والموظفون المخولون بمراجعة المدفوعات.',
    },
]);
</script>

<template>
    <Head title="دراسات جدوى مالية احترافية">
        <meta
            head-key="description"
            name="description"
            content="أنشئ دراسة جدوى مالية متكاملة لمشروعك خطوة بخطوة: قوائم مالية، تحليل سوق، تسعير بالتكلفة، وتقرير PDF احترافي."
        />
    </Head>

    <div class="overflow-x-clip">
        <HeroSection />
        <FeatureBento :draft-duration-days="draftDurationDays" />
        <ProcessTimeline
            :draft-duration-days="draftDurationDays"
            :formatted-price="formattedPrice"
        />
        <DashboardShowcase />
        <ReportPreview />
        <PricingSection
            :formatted-price="formattedPrice"
            :draft-duration-days="draftDurationDays"
        />
        <FaqSection :faqs="faqs" :contact="contact" />
        <FinalCta />
    </div>
</template>
