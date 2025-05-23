<script setup>
import { ref, reactive } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import CosmicAuthCard from "@/Components/Auth/CosmicAuthCard.vue";
import CosmicFormField from "@/Components/Auth/CosmicFormField.vue";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const authCardRef = ref(null);
const form = useForm({
    identifier: "", // ganti dari email
    password: "",
    remember: false,
});

const submit = () => {
    // Show quantum authentication processing effect
    authCardRef.value?.startProcessing();

    form.post(route("login"), {
        onFinish: () => form.reset("password"),
        onSuccess: () => {
            // Show success animation
            authCardRef.value?.showSuccess();
        },
        onError: () => {
            // Show failure animation
            authCardRef.value?.showFailure();
        },
    });
};
</script>

<template>
    <Head title="Log in" />

    <CosmicAuthCard
        ref="authCardRef"
        title="Log in to"
        subtitle="Access your cosmic journey"
    >
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <CosmicFormField
                id="identifier"
                type="text"
                label="Email / Username / Phone"
                v-model="form.identifier"
                :error="form.errors.identifier"
                required
                autofocus
                autocomplete="username"
            />

            <CosmicFormField
                id="password"
                type="password"
                label="Password"
                v-model="form.password"
                :error="form.errors.password"
                required
                autocomplete="current-password"
                :showPasswordToggle="true"
            />

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"
                        class="cosmic-checkbox"
                    />
                    <span class="ml-2 text-sm text-gray-300">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-gray-400 underline rounded-md hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ml-4 cosmic-button"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span>Log in</span>
                    <div class="cosmic-button-stars"></div>
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center">
                <span class="text-gray-400">Don't have an account?</span>
                <Link
                    :href="route('register')"
                    class="ml-1 underline transition-colors text-primary hover:text-primary-hover"
                >
                    Register
                </Link>
            </div>
        </form>
    </CosmicAuthCard>
</template>

<style scoped>
.cosmic-checkbox:checked {
    background-color: #9b87f5;
    border-color: #9b87f5;
}

.cosmic-button {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #9b87f5 0%, #33c3f0 100%);
    transition: all 0.3s ease;
}

.cosmic-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(155, 135, 245, 0.6);
}

.cosmic-button-stars {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(white 1px, transparent 1px);
    background-size: 15px 15px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cosmic-button:hover .cosmic-button-stars {
    opacity: 0.2;
    animation: warpSpeed 1s forwards;
}

@keyframes warpSpeed {
    0% {
        transform: translateX(0) scale(1);
    }
    100% {
        transform: translateX(100px) scale(2);
        opacity: 0;
    }
}
</style>
