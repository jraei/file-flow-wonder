<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    isFocused: {
        type: Boolean,
        default: false,
    },
});

const searchQuery = ref("");
const searchInput = ref(null);

const emit = defineEmits(["focus", "blur"]);

const handleFocus = () => {
    emit("focus");
};

const handleBlur = () => {
    emit("blur");
};
</script>

<template>
    <div
        class="relative transition-all duration-300"
        :class="{ 'shadow-glow-primary': isFocused }"
    >
        <div
            class="relative flex items-center transition-all rounded-full ring-1 ring-primary hover:ring-secondary"
            :class="{ 'ring-2 ring-primary': isFocused }"
        >
            <!-- Search Icon -->
            <div
                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
            </div>

            <!-- Search Input -->
            <input
                ref="searchInput"
                v-model="searchQuery"
                type="text"
                placeholder="Search across the cosmos..."
                class="w-full py-2 pl-10 pr-4 border-none rounded-full bg-primary/10 text-primary-text focus:ring-0 placeholder-primary-text/40"
                @focus="handleFocus"
                @blur="handleBlur"
            />

            <!-- Particle effects when focused -->
            <div
                v-if="isFocused"
                class="absolute inset-0 overflow-hidden pointer-events-none opacity-20"
            >
                <div
                    class="absolute top-0 w-1 h-1 rounded-full left-1/4 bg-secondary animate-ping"
                ></div>
                <div
                    class="absolute top-3/4 left-3/4 w-1.5 h-1.5 bg-primary rounded-full animate-pulse-slow"
                ></div>
                <div
                    class="absolute w-1 h-1 rounded-full top-1/2 left-1/3 bg-secondary animate-pulse"
                ></div>
                <div
                    class="absolute top-1/4 right-1/4 w-0.5 h-0.5 bg-primary rounded-full animate-ping"
                ></div>
            </div>
        </div>
    </div>
</template>
