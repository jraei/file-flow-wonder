<template>
    <div
        class="relative p-6 overflow-hidden shadow-lg rounded-xl bg-gradient-to-b from-primary/50 to-content_background/20 backdrop-blur-sm"
    >
        <div class="relative z-10">
            <h2 class="mb-4 text-2xl font-bold text-center text-white">
                Track Your Cosmic Transaction
            </h2>
            <p class="mb-6 text-center text-secondary">
                Enter your invoice ID to verify its status in the universe
            </p>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Enter invoice ID (e.g., TRX2023...)"
                        class="w-full px-4 py-3 text-white transition-all duration-300 border rounded-lg bg-dark-card/50 border-primary/30 focus:outline-none focus:border-secondary"
                        :class="{ 'animate-pulse-border': isFocused }"
                        @focus="handleFocus"
                        @blur="handleBlur"
                        @keyup.enter="handleSearch"
                    />

                    <!-- Focus effects -->
                    <div
                        v-if="isFocused"
                        class="absolute inset-0 rounded-lg pointer-events-none"
                    >
                        <div
                            class="absolute top-0 w-1 h-1 rounded-full left-1/4 bg-primary animate-ping"
                        ></div>
                        <div
                            class="absolute bottom-0 w-1 h-1 rounded-full right-1/3 bg-secondary animate-pulse"
                        ></div>
                    </div>
                </div>

                <button
                    @click="handleSearch"
                    :disabled="isSearching"
                    class="px-6 py-3 text-white transition-all duration-300 transform rounded-lg bg-gradient-to-r from-primary to-primary/80 hover:from-secondary hover:to-secondary/90 hover:scale-105 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <span v-if="!isSearching">Search Transaction</span>
                    <div v-else class="flex items-center justify-center">
                        <div
                            class="w-5 h-5 mr-2 border-2 border-white rounded-full border-t-transparent animate-spin"
                        ></div>
                        <span>Searching...</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "@/Composables/useToast";
import { debounce } from "lodash";

const props = defineProps({
    searchQuery: {
        type: String,
        default: "",
    },

    searchResult: {
        type: Object,
        default: null,
    },
});

const { toast } = useToast();
const searchInput = ref(props.searchQuery || "");
const isSearching = ref(false);
const isFocused = ref(false);
const { flash } = usePage().props;

// Debounced search input handling
const debouncedValidateInput = debounce((value) => {
    if (value.length > 0 && value.length < 4) {
        // Show quick validation feedback
        addQuantumGlowEffect("error");
    } else if (value.length >= 4) {
        addQuantumGlowEffect("success");
    }
}, 500);

// Watch for input changes to trigger validation
watch(searchInput, (newValue) => {
    debouncedValidateInput(newValue);
});

// Watch for errors from props
onMounted(() => {
    if (flash?.status?.type === "error") {
        toast.error(flash?.status?.text);
    }
});

const handleSearch = () => {
    if (!searchInput.value || searchInput.value.trim().length < 4) {
        toast.error("Please enter a valid transaction ID or invoice number");
        addQuantumGlowEffect("error");
        return;
    }

    isSearching.value = true;

    router.get(route("order.invoice", searchInput.value.trim()));
};

const handleFocus = () => {
    isFocused.value = true;
};

const handleBlur = () => {
    isFocused.value = false;
};

// Cosmic visual effects
const addQuantumGlowEffect = (type) => {
    const input = document.querySelector('input[placeholder*="invoice"]');
    if (!input) return;

    input.classList.add("quantum-glow");
    input.classList.add(
        type === "error" ? "quantum-glow-error" : "quantum-glow-success"
    );

    setTimeout(() => {
        input.classList.remove(
            "quantum-glow",
            "quantum-glow-error",
            "quantum-glow-success"
        );
    }, 1000);
};

onMounted(() => {
    // If there's already a search result, show meteor shower
    if (props.searchResult) {
        setTimeout(addMeteorShowerEffect, 500);
    }
});
</script>

<style scoped>
.animate-pulse-border {
    animation: pulseBorder 2s infinite;
    box-shadow: 0 0 15px 1px var(--color-primary);
}

@keyframes pulseBorder {
    0% {
        box-shadow: 0 0 5px 1px rgba(155, 135, 245, 0.5);
    }
    50% {
        box-shadow: 0 0 15px 3px rgba(155, 135, 245, 0.8);
    }
    100% {
        box-shadow: 0 0 5px 1px rgba(155, 135, 245, 0.5);
    }
}

.cosmic-particle {
    position: absolute;
    background-color: white;
    border-radius: 50%;
    opacity: 0.6;
    animation: float linear infinite;
}

@keyframes float {
    0% {
        transform: translateY(0) translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 0.8;
    }
    90% {
        opacity: 0.4;
    }
    100% {
        transform: translateY(-120px) translateX(20px);
        opacity: 0;
    }
}

.quantum-glow {
    transition: all 0.3s ease;
}

.quantum-glow-success {
    box-shadow: 0 0 15px 5px rgba(51, 195, 240, 0.7);
    border-color: rgba(51, 195, 240, 1) !important;
}

.quantum-glow-error {
    box-shadow: 0 0 15px 5px rgba(255, 71, 87, 0.7);
    border-color: rgba(255, 71, 87, 1) !important;
}

:global(.warping) {
    animation: warpEffect 0.5s forwards;
}

@keyframes warpEffect {
    0% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.5) blur(3px);
    }
    100% {
        filter: brightness(1) blur(0);
    }
}
</style>
