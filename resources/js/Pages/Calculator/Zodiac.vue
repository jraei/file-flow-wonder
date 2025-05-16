
<script setup>
import { ref, computed, watch } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import CosmicStarfield from "@/Components/User/Navigation/CosmicStarfield.vue";
import { Head } from "@inertiajs/vue3";

// State for the slider
const currentStars = ref(1);

// Calculate diamonds needed using the provided formula
const diamondsNeeded = computed(() => {
    const point = currentStars.value;
    
    // Already have max stars
    if (point >= 100) {
        return 0;
    }
    
    // Apply the provided formula
    if (point < 90) {
        return Math.ceil((2000 - point * 20) * 850 / 1000);
    } else {
        return Math.ceil(2000 - point * 20);
    }
});

// Format diamonds number with commas
const formattedDiamonds = computed(() => {
    return diamondsNeeded.value.toLocaleString();
});

// Result message based on stars and diamonds
const resultMessage = computed(() => {
    if (currentStars.value >= 100) {
        return "You already have enough stars for the Zodiac skin!";
    } else {
        return `You need ${formattedDiamonds.value} Diamonds to complete your Zodiac!`;
    }
});

// Percentage of progress toward the 100 stars goal
const progressPercentage = computed(() => {
    return (currentStars.value / 100) * 100;
});
</script>

<template>
    <Head title="Zodiac Calculator" />

    <GuestLayout>
        <div class="relative min-h-screen py-8 overflow-hidden">
            <!-- Background Effects -->
            <div class="absolute inset-0">
                <CosmicStarfield class="opacity-30" />
            </div>

            <!-- Orbital Circle Decoration -->
            <div
                class="absolute z-0 w-[800px] h-[800px] border border-secondary/20 rounded-full left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
            ></div>
            <div
                class="absolute z-0 w-[500px] h-[500px] border border-primary/30 rounded-full left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 animate-spin"
                style="animation-duration: 180s"
            ></div>

            <div class="container relative z-10 px-4 mx-auto max-w-7xl">
                <!-- Page Title -->
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-white md:text-4xl">
                        Zodiac <span class="text-secondary">Calculator</span>
                    </h1>
                    <p class="mt-2 text-white/70">
                        Calculate the diamonds needed to obtain a Zodiac skin
                    </p>
                </div>

                <!-- Main Calculator -->
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl">
                        <CosmicCard title="Zodiac Stars Calculator">
                            <div class="space-y-8">
                                <!-- Star Points Input -->
                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <label
                                            for="stars"
                                            class="text-lg font-medium text-white"
                                            >Current Star Points:</label
                                        >
                                        <div
                                            class="flex items-center space-x-1"
                                        >
                                            <span
                                                class="text-xl font-bold text-secondary"
                                                >{{ currentStars }}</span
                                            >
                                            <svg
                                                class="w-5 h-5 text-secondary"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
                                                />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Slider Component -->
                                    <div class="pt-4">
                                        <div
                                            class="relative h-2 mb-2 rounded-md bg-secondary/20"
                                        >
                                            <!-- Progress bar for the slider -->
                                            <div
                                                class="absolute h-2 rounded-md bg-gradient-to-r from-secondary to-primary"
                                                :style="`width: ${
                                                    (currentStars / 100) * 100
                                                }%`"
                                            ></div>

                                            <!-- Star points indicators -->
                                            <div
                                                class="absolute top-0 flex justify-between w-full transform -translate-y-4"
                                            >
                                                <span
                                                    class="text-xs text-secondary/60"
                                                    >1</span
                                                >
                                                <span
                                                    class="text-xs text-secondary/60"
                                                    >25</span
                                                >
                                                <span
                                                    class="text-xs text-secondary/60"
                                                    >50</span
                                                >
                                                <span
                                                    class="text-xs text-secondary/60"
                                                    >75</span
                                                >
                                                <span
                                                    class="text-xs text-secondary/60"
                                                    >100</span
                                                >
                                            </div>
                                        </div>

                                        <input
                                            id="stars"
                                            type="range"
                                            min="1"
                                            max="100"
                                            v-model="currentStars"
                                            class="w-full h-2 bg-transparent appearance-none cosmic-slider"
                                        />
                                    </div>

                                    <!-- Manual input -->
                                    <div
                                        class="flex items-center justify-center mt-4 space-x-3"
                                    >
                                        <button
                                            @click="
                                                currentStars = Math.max(
                                                    1,
                                                    currentStars - 1
                                                )
                                            "
                                            class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-secondary/20 hover:bg-secondary/40 focus:outline-none focus:ring-2 focus:ring-secondary/50"
                                        >
                                            -
                                        </button>

                                        <input
                                            type="number"
                                            v-model="currentStars"
                                            min="1"
                                            max="100"
                                            class="w-20 px-3 py-2 text-center text-white rounded-md bg-secondary/20 focus:outline-none focus:ring-2 focus:ring-primary"
                                        />

                                        <button
                                            @click="
                                                currentStars = Math.min(
                                                    100,
                                                    currentStars + 1
                                                )
                                            "
                                            class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-secondary/20 hover:bg-secondary/40 focus:outline-none focus:ring-2 focus:ring-secondary/50"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- Progress towards Zodiac Skin -->
                                <div class="p-4 rounded-lg bg-secondary/10">
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <span class="text-sm text-white/70"
                                            >Progress to Zodiac Skin:</span
                                        >
                                        <span
                                            class="text-sm font-bold text-white"
                                            >{{
                                                progressPercentage.toFixed(0)
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="w-full h-2 rounded-full bg-primary/20"
                                    >
                                        <div
                                            class="h-2 rounded-full bg-gradient-to-r from-primary to-secondary"
                                            :style="`width: ${progressPercentage}%`"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Results Display -->
                                <div
                                    class="p-6 text-center rounded-xl bg-primary/20"
                                >
                                    <div class="mb-3 text-sm text-white/70">
                                        You Need
                                    </div>
                                    <div
                                        class="flex items-center justify-center mb-3"
                                    >
                                        <svg
                                            class="w-6 h-6 mr-2 text-primary"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                        >
                                            <path
                                                d="M12 2L9.5 8.5H2L7.5 12.5L5.5 19L12 15L18.5 19L16.5 12.5L22 8.5H14.5L12 2Z"
                                            />
                                        </svg>
                                        <span
                                            class="text-3xl font-bold text-white"
                                            >{{ formattedDiamonds }}</span
                                        >
                                        <svg
                                            class="w-6 h-6 ml-2 text-secondary"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                        >
                                            <path
                                                d="M12 2L9.5 8.5H2L7.5 12.5L5.5 19L12 15L18.5 19L16.5 12.5L22 8.5H14.5L12 2Z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="text-lg text-white">
                                        {{ resultMessage }}
                                    </div>
                                </div>

                                <!-- CTA Button -->
                                <div class="text-center">
                                    <a
                                        href="#"
                                        class="inline-flex items-center px-6 py-3 font-bold transition-all duration-200 rounded-lg shadow-lg bg-gradient-to-r from-secondary to-primary hover:shadow-glow-secondary"
                                    >
                                        <svg
                                            class="w-5 h-5 mr-2"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                        >
                                            <path
                                                d="M12 2L9.5 8.5H2L7.5 12.5L5.5 19L12 15L18.5 19L16.5 12.5L22 8.5H14.5L12 2Z"
                                            />
                                        </svg>
                                        Top Up Diamonds Now!
                                    </a>
                                </div>
                            </div>
                        </CosmicCard>

                        <!-- Info Box -->
                        <div class="p-4 mt-8 rounded-lg bg-secondary/10">
                            <h3 class="mb-2 text-lg font-semibold text-white">
                                How it works
                            </h3>
                            <p class="text-sm text-white/70">
                                The Zodiac draw requires 100 stars to exchange
                                for a Zodiac skin. This calculator helps you
                                estimate how many more diamonds you need based on
                                your current star count. The diamond cost
                                calculation changes as you get closer to 90
                                stars.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.cosmic-slider {
    @apply cursor-pointer;
}

/* Custom slider styling */
.cosmic-slider::-webkit-slider-thumb {
    @apply appearance-none w-6 h-6 rounded-full bg-gradient-to-br from-secondary to-primary cursor-pointer;
    box-shadow: 0 0 10px rgba(51, 195, 240, 0.6);
}

.cosmic-slider::-moz-range-thumb {
    @apply w-6 h-6 border-0 rounded-full bg-gradient-to-br from-secondary to-primary cursor-pointer;
    box-shadow: 0 0 10px rgba(51, 195, 240, 0.6);
}

/* Shadow glow for the CTA button */
.shadow-glow-secondary {
    box-shadow: 0 0 15px rgba(51, 195, 240, 0.6);
}
</style>
