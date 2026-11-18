<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { KeyRound, Lock, LockKeyhole, Mail } from '@lucide/vue';
import { ref } from 'vue';
import AuthField from '@/components/auth/AuthField.vue';
import { authButtonClass, authInputClass } from '@/components/auth/authStyles';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'تعيين كلمة مرور جديدة',
        description: 'اختر كلمة مرور قوية لم تستخدمها من قبل.',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="إعادة تعيين كلمة المرور" />

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
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
                autocomplete="email"
                v-model="inputEmail"
                :class="[authInputClass, 'bg-muted/60 text-muted-foreground']"
                readonly
            />
        </AuthField>

        <AuthField
            id="password"
            label="كلمة المرور الجديدة"
            :icon="Lock"
            :error="errors.password"
        >
            <PasswordInput
                id="password"
                name="password"
                autocomplete="new-password"
                autofocus
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
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="••••••••"
                :passwordrules="passwordRules"
                :class="authInputClass"
            />
        </AuthField>

        <Button
            type="submit"
            :class="[authButtonClass, 'mt-1']"
            :disabled="processing"
            data-test="reset-password-button"
        >
            <Spinner v-if="processing" />
            <KeyRound v-else class="size-4" />
            حفظ كلمة المرور الجديدة
        </Button>
    </Form>
</template>
