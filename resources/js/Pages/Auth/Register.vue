<script setup>
import { ref, reactive, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import CosmicAuthCard from "@/Components/Auth/CosmicAuthCard.vue";
import CosmicFormField from "@/Components/Auth/CosmicFormField.vue";
import CountrySelector from "@/Components/Auth/CountrySelector.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const authCardRef = ref(null);
const form = useForm({
    name: "",
    email: "",
    username: "",
    phone_code: "+62", // Default country code for Indonesia
    phone: "",
    password: "",
    password_confirmation: "",
});

// make phone value reactive based on selected country code
watch(
    () => form.phone_code,
    (value) => {
        form.phone = value;
    },
    { immediate: true }
);

// Password strength indicator
const passwordStrength = ref(0);
const passwordFeedback = ref("");
const passwordStrengthClasses = reactive({
    0: "bg-gray-300 w-0",
    1: "bg-red-500 w-1/4",
    2: "bg-orange-500 w-2/4",
    3: "bg-yellow-500 w-3/4",
    4: "bg-green-500 w-full",
});

const passwordStrengthText = reactive({
    0: "",
    1: "Weak",
    2: "Fair",
    3: "Good",
    4: "Strong",
});

// Simple password strength checker
function checkPasswordStrength(password) {
    if (!password) {
        passwordStrength.value = 0;
        passwordFeedback.value = "";
        return;
    }

    let score = 0;

    // Length check
    if (password.length >= 8) score++;
    else {
        passwordFeedback.value = "Password should be at least 8 characters";
        passwordStrength.value = Math.max(1, score);
        return;
    }

    // Complexity checks
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    passwordStrength.value = score;

    if (score < 3) {
        passwordFeedback.value = "Add uppercase, numbers or special characters";
    } else {
        passwordFeedback.value = "";
    }
}

const submit = () => {
    // Add country code to phone
    const formattedPhone = form.phone ? `${form.phone_code}${form.phone}` : "";

    // Show quantum authentication processing effect
    authCardRef.value?.startProcessing();

    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
        onSuccess: () => {
            // Show success animation
            authCardRef.value?.showSuccess();
        },
        onError: () => {
            // Show failure animation
            authCardRef.value?.showFailure();
        },
        data: {
            name: form.name,
            email: form.email,
            username: form.username,
            phone: formattedPhone,
            password: form.password,
            password_confirmation: form.password_confirmation,
        },
    });
};
</script>

<template>
    <Head title="Register" />

    <CosmicAuthCard
        ref="authCardRef"
        title="Create Your"
        subtitle="Join the cosmic community"
    >
        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <CosmicFormField
                    id="name"
                    type="text"
                    label="Full Name"
                    v-model="form.name"
                    :error="form.errors.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <CosmicFormField
                    id="username"
                    type="text"
                    label="Username"
                    v-model="form.username"
                    :error="form.errors.username"
                    required
                    autocomplete="username"
                />
                <div class="col-span-1 sm:col-span-2">
                    <CosmicFormField
                        id="email"
                        type="email"
                        label="Email"
                        v-model="form.email"
                        :error="form.errors.email"
                        required
                        autocomplete="email"
                    />
                </div>

                <CountrySelector
                    id="phone_code"
                    v-model="form.phone_code"
                    label="Country Code"
                />

                <CosmicFormField
                    id="phone"
                    type="text"
                    label="WhatsApp Number"
                    v-model="form.phone"
                    :error="form.errors.phone"
                    autocomplete="tel"
                />

                <div class="col-span-1 sm:col-span-2">
                    <CosmicFormField
                        id="password"
                        type="password"
                        label="Password"
                        v-model="form.password"
                        :error="form.errors.password"
                        required
                        autocomplete="new-password"
                        :showPasswordToggle="true"
                        @update:modelValue="checkPasswordStrength"
                    />

                    <!-- Password strength meter -->
                    <div v-if="form.password" class="mt-1">
                        <div
                            class="w-full h-1.5 bg-gray-700 rounded-full overflow-hidden"
                        >
                            <div
                                class="h-full transition-all duration-300"
                                :class="
                                    passwordStrengthClasses[passwordStrength]
                                "
                            ></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <p
                                class="text-xs text-gray-400"
                                v-if="passwordFeedback"
                            >
                                {{ passwordFeedback }}
                            </p>
                            <p
                                class="text-xs"
                                :class="{
                                    'text-red-500': passwordStrength === 1,
                                    'text-orange-500': passwordStrength === 2,
                                    'text-yellow-500': passwordStrength === 3,
                                    'text-green-500': passwordStrength === 4,
                                }"
                                v-if="passwordStrength > 0"
                            >
                                {{ passwordStrengthText[passwordStrength] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <CosmicFormField
                        id="password_confirmation"
                        type="password"
                        label="Confirm Password"
                        v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation"
                        required
                        autocomplete="new-password"
                        :showPasswordToggle="true"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link
                    :href="route('login')"
                    class="text-sm text-gray-400 underline rounded-md hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ml-4 cosmic-button"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span>Register</span>
                    <div class="cosmic-button-stars"></div>
                </PrimaryButton>
            </div>
        </form>
    </CosmicAuthCard>
</template>

<style scoped>
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
