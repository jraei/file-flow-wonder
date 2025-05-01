<template>
    <div class="absolute inset-0 pointer-events-none planets-container z-15">
        <!-- SVG definitions for Saturn -->
        <svg class="absolute" width="0" height="0">
            <defs>
                <!-- Saturn ring pattern -->
                <linearGradient
                    id="saturn-ring-gradient"
                    x1="0%"
                    y1="0%"
                    x2="100%"
                    y2="0%"
                >
                    <stop offset="10%" stop-color="transparent" />
                    <stop offset="20%" stop-color="rgba(155, 135, 245, 0.3)" />
                    <stop offset="30%" stop-color="rgba(155, 135, 245, 0.3)" />
                    <stop offset="40%" stop-color="rgba(51, 195, 240, 0.2)" />
                    <stop offset="60%" stop-color="rgba(51, 195, 240, 0.2)" />
                    <stop offset="70%" stop-color="rgba(155, 135, 245, 0.3)" />
                    <stop offset="80%" stop-color="rgba(155, 135, 245, 0.3)" />
                    <stop offset="90%" stop-color="transparent" />
                </linearGradient>

                <!-- Saturn gradient -->
                <linearGradient id="saturn-gradient">
                    <stop offset="0%" stop-color="rgba(227, 190, 150, 0.9)" />
                    <stop offset="100%" stop-color="rgba(209, 165, 101, 0.3)" />
                </linearGradient>

                <!-- Filter for glow effect -->
                <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur
                        in="SourceGraphic"
                        stdDeviation="5"
                        result="blur"
                    />
                    <feComposite
                        in="SourceGraphic"
                        in2="blur"
                        operator="over"
                    />
                </filter>
            </defs>
        </svg>

        <!-- Fixed Saturn-like planet -->
        <div
            class="fixed pointer-events-none saturn-planet"
            :class="{ 'saturn-mobile': isMobile }"
            :style="saturnStyle"
        >
            <!-- SVG Saturn -->
            <svg
                :width="saturnSize"
                :height="saturnSize"
                viewBox="0 0 100 100"
                class="absolute inset-0"
                style="will-change: transform"
            >
                <!-- Saturn body -->
                <circle
                    cx="50"
                    cy="50"
                    :r="saturnSize / 2 - 5"
                    fill="url(#saturn-gradient)"
                    :filter="isLowPowerDevice ? '' : 'url(#glow)'"
                />

                <!-- Saturn ring system (when not in reduced motion) -->
                <ellipse
                    v-if="!isReducedMotion"
                    cx="50"
                    cy="50"
                    rx="55"
                    ry="20"
                    fill="none"
                    stroke="url(#saturn-ring-gradient)"
                    stroke-width="4"
                    transform="rotate(23.5)"
                    opacity="0.7"
                />
            </svg>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

// Device detection
const isMobile = ref(false);
const isLowPowerDevice = computed(() => {
    return navigator.hardwareConcurrency
        ? navigator.hardwareConcurrency < 4
        : isMobile.value;
});

// Check for reduced motion preference
const isReducedMotion = computed(() => {
    return (
        window?.matchMedia("(prefers-reduced-motion: reduce)")?.matches || false
    );
});

// Saturn planet properties with responsive sizing
const saturnSize = computed(() => (isMobile.value ? 80 : 100));

// Saturn styles
const saturnStyle = computed(() => ({
    width: `${saturnSize.value}px`,
    height: `${saturnSize.value}px`,
    right: isMobile.value ? "1px" : "3%",
    top: isMobile.value ? "50px" : "120px",
    opacity: 0.7,
    transform: "translateZ(0)",
    willChange: "transform",
    animation: isReducedMotion.value
        ? "none"
        : "saturn-rotate 120s linear infinite",
}));

// Visibility observer for performance
let observer = null;
const isVisible = ref(false);

// Update device detection
const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    handleResize();
    window.addEventListener("resize", handleResize);

    // Setup intersection observer to pause animation when not visible
    observer = new IntersectionObserver((entries) => {
        const [entry] = entries;
        isVisible.value = entry.isIntersecting;

        // Pause animation when not visible
        const saturn = document.querySelector(".saturn-planet");
        if (saturn) {
            if (!entry.isIntersecting) {
                saturn.style.animationPlayState = "paused";
            } else {
                saturn.style.animationPlayState = "running";
            }
        }
    });

    const container = document.querySelector(".planets-container");
    if (container) {
        observer.observe(container);
    }
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
    if (observer) {
        observer.disconnect();
    }
});
</script>

<style scoped>
.planets-container {
    overflow: hidden;
    z-index: 15;
}

.saturn-planet {
    will-change: transform;
    transform: translateZ(0); /* Hardware acceleration */
}

.saturn-mobile {
    transform: scale(0.7) translateZ(0);
}

@keyframes saturn-rotate {
    0% {
        transform: rotate(0deg) translateZ(0);
    }
    100% {
        transform: rotate(360deg) translateZ(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .saturn-planet {
        animation: none !important;
    }
}
</style>
