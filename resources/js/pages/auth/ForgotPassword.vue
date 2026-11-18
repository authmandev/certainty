<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, Mail, Send } from '@lucide/vue';
import AuthField from '@/components/auth/AuthField.vue';
import AuthStatus from '@/components/auth/AuthStatus.vue';
import { authButtonClass, authInputClass } from '@/components/auth/authStyles';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'استعادة كلمة المرور',
        description:
            'أدخل بريدك الإلكتروني وسنرسل لك رابطًا آمنًا لإعادة تعيين كلمة المرور.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="نسيت كلمة المرور" />

    <div>
        <AuthStatus :message="status" />

        <Form
            v-bind="email.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <AuthField
                id="email"
                label="البريد الإلكتروني"
                :icon="Mail"
                :error="errors.email"
            >
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    v-focus
                    placeholder="email@example.com"
                    :class="authInputClass"
                />
            </AuthField>

            <Button
                :class="authButtonClass"
                :disabled="processing"
                data-test="email-password-reset-link-button"
            >
                <Spinner v-if="processing" />
                <Send v-else class="size-4" />
                إرسال رابط إعادة التعيين
            </Button>
        </Form>

        <div class="mt-6 border-t pt-5 text-center text-sm">
            <TextLink
                :href="login()"
                class="inline-flex items-center gap-1.5 text-muted-foreground no-underline hover:text-foreground"
            >
                <ArrowRight class="size-4" />
                العودة إلى تسجيل الدخول
            </TextLink>
        </div>
    </div>
</template>
