<script setup>
import { ref, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: "text",
    },
    modelValue: {
        type: [String, Number],
        default: "",
    },
    label: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: "",
    },
    autocomplete: {
        type: String,
        default: "",
    },
    required: {
        type: Boolean,
        default: false,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    showPasswordToggle: {
        type: Boolean,
        default: false,
    },
});

const modelValue = defineModel({
    type: String,
    required: true,
});

const emit = defineEmits(["update:modelValue"]);

const inputRef = ref(null);
const localType = ref(props.type);
const isFocused = ref(false);
const particles = ref([]);
const particleCount = 5;

onMounted(() => {
    generateParticles();
});

function generateParticles() {
    particles.value = [];
    for (let i = 0; i < particleCount; i++) {
        particles.value.push({
            size: Math.random() * 3 + 1,
            x: Math.random() * 100,
            y: Math.random() * 100,
            animationDelay: Math.random() * 2,
            animationDuration: Math.random() * 3 + 2,
        });
    }
}

function onFocus() {
    isFocused.value = true;
}

function onBlur() {
    isFocused.value = false;
}

function togglePasswordVisibility() {
    localType.value = localType.value === "password" ? "text" : "password";
}

defineExpose({
    focus: () => inputRef.value?.focus(),
});
</script>

<template>
    <div class="relative mb-2 cosmic-form-field">
        <InputLabel :for="id" :value="label" class="text-gray-300" />

        <div class="relative">
            <TextInput
                :id="id"
                :name="id"
                :type="localType"
                :value="modelValue"
                :required="required"
                :autofocus="autofocus"
                :autocomplete="autocomplete"
                ref="inputRef"
                v-model="modelValue"
                @focus="onFocus"
                @blur="onBlur"
                class="block w-full mt-1 text-white border-0 cosmic-input bg-dark-lighter focus:border-primary focus:ring-2 focus:ring-primary"
                :class="{ 'pr-10': showPasswordToggle }"
            />

            <!-- Password toggle eye icon -->
            <div
                v-if="showPasswordToggle"
                class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                @click="togglePasswordVisibility"
            >
                <svg
                    v-if="localType === 'password'"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-5 h-5 text-gray-400 transition-colors hover:text-gray-200"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-5 h-5 text-gray-400 transition-colors hover:text-gray-200"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
                    />
                </svg>
            </div>

            <!-- Gravitational particles -->
            <div
                class="absolute inset-0 transition-all duration-300 transform scale-90 opacity-0 pointer-events-none cosmic-gravitational-particles"
                :class="{ active: isFocused }"
            >
                <div
                    v-for="(particle, index) in particles"
                    :key="index"
                    class="absolute rounded-full particle bg-primary"
                    :style="{
                        width: `${particle.size}px`,
                        height: `${particle.size}px`,
                        left: `${particle.x}%`,
                        top: `${particle.y}%`,
                        opacity: isFocused ? 0.7 : 0,
                        animationDelay: `${particle.animationDelay}s`,
                        animationDuration: `${particle.animationDuration}s`,
                    }"
                ></div>
            </div>

            <div
                class="absolute inset-0 rounded-md pointer-events-none input-focus-pulse"
                :class="{ active: isFocused }"
            ></div>
        </div>

        <InputError :message="error" class="mt-2" />
    </div>
</template>

<style scoped>
.cosmic-input {
    background-color: rgba(31, 41, 55, 0.8);
    border: 1px solid rgba(155, 135, 245, 0.2);
    transition: all 0.3s ease;
}

.cosmic-input:focus {
    box-shadow: 0 0 0 2px rgba(155, 135, 245, 0.3),
        0 0 15px rgba(155, 135, 245, 0.2);
    border-color: rgba(155, 135, 245, 0.5);
}

.cosmic-gravitational-particles.active {
    opacity: 0.8;
    transform: scale(1);
}

.particle {
    animation: float 3s ease-in-out infinite alternate;
    box-shadow: 0 0 8px rgba(155, 135, 245, 0.6);
}

.input-focus-pulse {
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.input-focus-pulse.active {
    border-color: rgba(155, 135, 245, 0.4);
    box-shadow: 0 0 15px rgba(155, 135, 245, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(155, 135, 245, 0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(155, 135, 245, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(155, 135, 245, 0);
    }
}

@keyframes float {
    0% {
        transform: translate(0, 0);
    }
    50% {
        transform: translate(5px, -5px);
    }
    100% {
        transform: translate(-5px, 5px);
    }
}
</style>
