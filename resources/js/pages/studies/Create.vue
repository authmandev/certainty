<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import StudyController from '@/actions/App/Http/Controllers/StudyController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatCurrency } from '@/lib/format';
import { dashboard } from '@/routes';
import { create } from '@/routes/studies';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'دراساتي', href: dashboard() },
            { title: 'دراسة جديدة', href: create() },
        ],
    },
});

const props = defineProps<{
    price: number;
    currency: string;
    draftDurationDays: number;
}>();

const formattedPrice = computed(() =>
    formatCurrency(props.price, props.currency),
);
</script>

<template>
    <Head title="دراسة جديدة" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="دراسة جدوى جديدة"
            description="ابدأ بتسمية دراستك ومشروعك، وأكمل باقي البيانات بعد الإنشاء"
        />

        <div class="grid gap-6 lg:grid-cols-3">
            <Form
                v-bind="StudyController.store.form()"
                class="space-y-6 rounded-2xl border bg-card p-6 lg:col-span-2"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="title">اسم الدراسة</Label>
                    <Input
                        id="title"
                        name="title"
                        required
                        v-focus
                        placeholder="مثال: دراسة جدوى مقهى مختص"
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="project_name">اسم المشروع</Label>
                    <Input
                        id="project_name"
                        name="project_name"
                        required
                        placeholder="مثال: مقهى الصباح"
                    />
                    <InputError :message="errors.project_name" />
                </div>

                <Button
                    type="submit"
                    :disabled="processing"
                    data-test="create-study-button"
                >
                    <Spinner v-if="processing" />
                    إنشاء الدراسة
                </Button>
            </Form>

            <aside class="space-y-4 rounded-2xl border bg-muted/40 p-6 text-sm">
                <div>
                    <p class="text-muted-foreground">رسوم الدراسة</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums">
                        {{ formattedPrice }}
                    </p>
                </div>
                <ul class="space-y-2 leading-relaxed text-muted-foreground">
                    <li>
                        • لديك {{ draftDurationDays }} يومًا من تاريخ الإنشاء
                        لإكمال الدراسة.
                    </li>
                    <li>
                        • يمكنك إدخال البيانات ومشاهدة الملخص المالي قبل اعتماد
                        الدفع.
                    </li>
                    <li>
                        • الدفع بالتحويل البنكي، ويلزم اعتماده لإنهاء الدراسة
                        وإصدار التقرير.
                    </li>
                    <li>• تُضاف الرسوم تلقائيًا إلى تكاليف التأسيس.</li>
                </ul>
            </aside>
        </div>
    </div>
</template>
