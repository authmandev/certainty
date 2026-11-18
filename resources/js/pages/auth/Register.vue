<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft, Lock, LockKeyhole, Mail, UserRound } from '@lucide/vue';
import AuthField from '@/components/auth/AuthField.vue';
import { authButtonClass, authInputClass } from '@/components/auth/authStyles';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { login, terms } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'ابدأ أول دراسة جدوى لمشروعك',
        description:
            'أنشئ حسابك مجانًا وابدأ بإدخال بياناتك فورًا، وادفع فقط قبل إصدار التقرير النهائي.',
    },
});
</script>

<template>
    <Head title="إنشاء حساب" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <AuthField
            id="name"
            label="الاسم الكامل"
            :icon="UserRound"
            :error="errors.name"
        >
            <Input
                id="name"
                type="text"
                required
                v-focus
                :tabindex="1"
                autocomplete="name"
                name="name"
                placeholder="مثال: محمد العتيبي"
                :class="authInputClass"
            />
        </AuthField>

        <AuthField
            id="email"
            label="البريد الإلكتروني"
            :icon="Mail"
            :error="errors.email"
        >
            <Input
                id="email"
                type="email"
                required
                :tabindex="2"
                autocomplete="email"
                name="email"
                placeholder="email@example.com"
                :class="authInputClass"
            />
        </AuthField>

        <div class="grid gap-5 sm:grid-cols-2">
            <AuthField
                id="password"
                label="كلمة المرور"
                :icon="Lock"
                :error="errors.password"
            >
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="••••••••"
                    :passwordrules="passwordRules"
                    :class="authInputClass"
                />
            </AuthField>

            <AuthField
                id="password_confirmation"
                label="تأكيد كلمة المرور"
                :icon="LockKeyhole"
                :error="errors.password_confirmation"
            >
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="••••••••"
                    :passwordrules="passwordRules"
                    :class="authInputClass"
                />
            </AuthField>
        </div>

        <Button
            type="submit"
            :class="[authButtonClass, 'mt-1']"
            tabindex="5"
            :disabled="processing"
            data-test="register-user-button"
        >
            <Spinner v-if="processing" />
            إنشاء الحساب
            <ArrowLeft
                v-if="!processing"
                class="size-4 transition-transform group-hover:-translate-x-1"
            />
        </Button>

        <p class="text-center text-xs leading-relaxed text-muted-foreground">
            بإنشاء حسابك فإنك توافق على
            <TextLink :href="terms()" :tabindex="7">الشروط والأحكام</TextLink>.
        </p>

        <div class="border-t pt-5 text-center text-sm text-muted-foreground">
            لديك حساب بالفعل؟
            <TextLink :href="login()" class="font-medium" :tabindex="6"
                >تسجيل الدخول</TextLink
            >
        </div>
    </Form>
</template>
