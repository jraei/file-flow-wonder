<script setup>
// Import any necessary dependencies
import { ref } from "vue";
import CosmicStarfield from "../Navigation/CosmicStarfield.vue";

// For the satellite beacon effect
const { judulWeb } = defineProps({
    judulWeb: String,
});

const beaconPulse = ref(true);

// Toggle the beacon state every 2 seconds
setInterval(() => {
    beaconPulse.value = !beaconPulse.value;
}, 2000);

const currentYear = new Date().getFullYear();
</script>

<template>
    <div
        class="relative py-6 overflow-hidden text-sm bg-gradient-to-b bg-footer_background text-primary-text/80"
    >
        <div class="absolute inset-0 overflow-hidden">
            <CosmicStarfield class="opacity-30" />
        </div>

        <!-- Background constellation pattern -->
        <div class="absolute inset-0 overflow-hidden opacity-5">
            <!-- Connect stars with lines to form constellation -->
            <svg
                viewBox="0 0 100 12"
                width="100%"
                height="100%"
                preserveAspectRatio="none"
                class="absolute inset-0"
            >
                <line
                    x1="10"
                    y1="2"
                    x2="20"
                    y2="6"
                    stroke="white"
                    stroke-width="0.1"
                />
                <line
                    x1="20"
                    y1="6"
                    x2="30"
                    y2="3"
                    stroke="white"
                    stroke-width="0.1"
                />
                <line
                    x1="30"
                    y1="3"
                    x2="40"
                    y2="8"
                    stroke="white"
                    stroke-width="0.1"
                />
                <line
                    x1="40"
                    y1="8"
                    x2="60"
                    y2="5"
                    stroke="white"
                    stroke-width="0.1"
                />
                <line
                    x1="60"
                    y1="5"
                    x2="70"
                    y2="9"
                    stroke="white"
                    stroke-width="0.1"
                />
                <line
                    x1="70"
                    y1="9"
                    x2="90"
                    y2="4"
                    stroke="white"
                    stroke-width="0.1"
                />

                <!-- Stars at connection points -->
                <circle cx="10" cy="2" r="0.4" fill="white" />
                <circle cx="20" cy="6" r="0.3" fill="white" />
                <circle cx="30" cy="3" r="0.5" fill="white" />
                <circle cx="40" cy="8" r="0.3" fill="white" />
                <circle cx="60" cy="5" r="0.4" fill="white" />
                <circle cx="70" cy="9" r="0.3" fill="white" />
                <circle cx="90" cy="4" r="0.5" fill="white" />
            </svg>
        </div>

        <div
            class="relative z-10 flex flex-col items-center justify-between px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 sm:flex-row"
        >
            <!-- Copyright text with cosmic symbol -->
            <div class="flex items-center mb-4 sm:mb-0">
                <span class="mr-2 text-primary-text">&#x1F7BC;</span>
                <!-- Unicode symbol for cosmic copyright -->
                <span
                    >{{ currentYear }} {{ judulWeb }} - Across the
                    Universe</span
                >
            </div>

            <!-- Satellite beacon -->
            <div class="relative flex items-center">
                <!-- Satellite -->
                <div class="relative mr-3">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M4 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>
                        <path d="M12 4a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>
                        <path d="M20 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>
                        <path d="M12 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>
                        <path d="M6 12h4"></path>
                        <path d="M14 12h4"></path>
                        <path d="M12 6v4"></path>
                        <path d="M12 14v4"></path>
                    </svg>

                    <!-- Beacon pulse -->
                    <div
                        class="absolute w-10 h-10 transition-opacity duration-1000 transform -translate-x-1/2 -translate-y-1/2 rounded-full top-1/2 left-1/2"
                        :class="beaconPulse ? 'opacity-40' : 'opacity-0'"
                        style="
                            background: radial-gradient(
                                circle,
                                rgba(155, 135, 245, 0.3) 0%,
                                rgba(155, 135, 245, 0) 70%
                            );
                        "
                    ></div>
                </div>

                <span
                    >Developed by
                    <a
                        href="https://instagram.com/justin_reifan"
                        target="_blank"
                        class="font-bold cursor-pointer hover:underline"
                        >Justin Rei</a
                    ></span
                >
            </div>
        </div>

        <!-- Occasional shooting star (appears randomly) -->
        <div
            v-if="Math.random() > 0.7"
            class="absolute h-px bg-white shadow-lg"
            :style="{
                width: '100px',
                top: `${Math.random() * 100}%`,
                left: '-100px',
                transform: 'rotate(15deg)',
                animation: 'shootingStar 2s linear forwards',
            }"
        ></div>
    </div>
</template>

<style scoped>
@keyframes shootingStar {
    from {
        left: -100px;
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    to {
        left: "calc(100% + 100px)";
        opacity: 0;
    }
}
</style>
