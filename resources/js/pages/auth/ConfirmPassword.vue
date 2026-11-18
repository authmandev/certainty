<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Lock, ShieldCheck } from '@lucide/vue';
import AuthField from '@/components/auth/AuthField.vue';
import { authButtonClass, authInputClass } from '@/components/auth/authStyles';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: {
        title: 'تأكيد كلمة المرور',
        description:
            'هذه منطقة محمية من التطبيق. يرجى تأكيد كلمة المرور للمتابعة.',
    },
});
</script>

<template>
    <Head title="تأكيد كلمة المرور" />

    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div
            class="flex items-center gap-3 rounded-xl bg-primary/10 px-4 py-3 text-sm text-primary"
        >
            <ShieldCheck class="size-5 shrink-0" />
            نطلب التأكيد لحماية إعدادات حسابك الحساسة.
        </div>

        <AuthField
            id="password"
            label="كلمة المرور"
            :icon="Lock"
            :error="errors.password"
        >
            <PasswordInput
                id="password"
                name="password"
                required
                autocomplete="current-password"
                autofocus
                placeholder="••••••••"
                :class="authInputClass"
            />
        </AuthField>

        <Button
            :class="authButtonClass"
            :disabled="processing"
            data-test="confirm-password-button"
        >
            <Spinner v-if="processing" />
            تأكيد والمتابعة
        </Button>
    </Form>
</template>
