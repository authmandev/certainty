<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft, Lock, Mail } from '@lucide/vue';
import AuthField from '@/components/auth/AuthField.vue';
import AuthStatus from '@/components/auth/AuthStatus.vue';
import { authButtonClass, authInputClass } from '@/components/auth/authStyles';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'مرحبًا بعودتك',
        description: 'سجّل الدخول لمتابعة دراسات الجدوى الخاصة بك.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="تسجيل الدخول" />

    <div>
        <AuthStatus :message="status" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
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
                    required
                    v-focus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                    :class="authInputClass"
                />
            </AuthField>

            <AuthField
                id="password"
                label="كلمة المرور"
                :icon="Lock"
                :error="errors.password"
            >
                <template #aside>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs"
                        :tabindex="5"
                    >
                        نسيت كلمة المرور؟
                    </TextLink>
                </template>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    :class="authInputClass"
                />
            </AuthField>

            <Label
                for="remember"
                class="flex w-fit cursor-pointer items-center gap-3 font-normal text-muted-foreground"
            >
                <Checkbox id="remember" name="remember" :tabindex="3" />
                <span>تذكرني على هذا الجهاز</span>
            </Label>

            <Button
                type="submit"
                :class="authButtonClass"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                تسجيل الدخول
                <ArrowLeft
                    v-if="!processing"
                    class="size-4 transition-transform group-hover:-translate-x-1"
                />
            </Button>

            <div class="relative text-center text-sm">
                <div class="absolute inset-x-0 top-1/2 h-px bg-border"></div>
                <span class="relative bg-card px-3 text-muted-foreground"
                    >جديد على المنصة؟</span
                >
            </div>

            <Button
                variant="outline"
                class="h-12 w-full rounded-xl text-base"
                as-child
            >
                <TextLink :href="register()" :tabindex="5" class="no-underline"
                    >إنشاء حساب جديد</TextLink
                >
            </Button>
        </Form>
    </div>
</template>
