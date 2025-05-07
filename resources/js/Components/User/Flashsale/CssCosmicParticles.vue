<template>
    <div
        class="cosmic-particles-container"
        :class="[theme === 'secondary' ? 'theme-secondary' : 'theme-primary']"
    >
        <div class="stars-container">
            <div
                v-for="i in starCount"
                :key="`star-${i}`"
                class="star"
                :style="getRandomStarStyle()"
            ></div>
        </div>
        <!-- <div class="glow-overlay"></div> -->
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

const props = defineProps({
    theme: {
        type: String,
        default: "primary", // 'primary' or 'secondary'
    },
    density: {
        type: Number,
        default: 1, // 1 = normal, 0.5 = reduced, etc.
    },
});

// Determine particle count based on device capability
const isMobile = ref(window.innerWidth < 768);
const isLowPowerDevice = ref(
    navigator.hardwareConcurrency
        ? navigator.hardwareConcurrency < 4
        : isMobile.value
);

// Compute how many stars to show based on density and device
const starCount = computed(() => {
    const base = isMobile.value ? 8 : isLowPowerDevice.value ? 12 : 20;
    return Math.floor(base * props.density);
});

// Generate random style for each star
const getRandomStarStyle = () => {
    const size = Math.random() * 2 + 0.5; // 0.5-2.5px
    const x = Math.random() * 100; // position in %
    const y = Math.random() * 100;
    const delay = Math.random() * 5; // animation delay in seconds
    const duration = 3 + Math.random() * 4; // animation duration in seconds

    return {
        width: `${size}px`,
        height: `${size}px`,
        left: `${x}%`,
        top: `${y}%`,
        opacity: 0.3 + Math.random() * 0.7,
        animationDelay: `${delay}s`,
        animationDuration: `${duration}s`,
    };
};
</script>

<style scoped>
.cosmic-particles-container {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.stars-container {
    position: absolute;
    width: 100%;
    height: 100%;
}

.star {
    position: absolute;
    border-radius: 50%;
    animation: pulse-star infinite ease-in-out alternate;
    will-change: opacity;
}

.theme-primary .star {
    background-color: #9b87f5; /* primary */
    box-shadow: 0 0 5px rgba(155, 135, 245, 0.7);
}

.theme-secondary .star {
    background-color: #33c3f0; /* secondary */
    box-shadow: 0 0 5px rgba(51, 195, 240, 0.7);
}

.glow-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(
        circle at 50% 50%,
        transparent 70%,
        rgba(155, 135, 245, 0.2) 100%
    );
}

.theme-secondary .glow-overlay {
    background: radial-gradient(
        circle at 50% 50%,
        transparent 70%,
        rgba(51, 195, 240, 0.2) 100%
    );
}

@keyframes pulse-star {
    0% {
        transform: scale(0.8);
        opacity: 0.3;
    }
    100% {
        transform: scale(1.2);
        opacity: 0.9;
    }
}

/* Prevent animations for users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
    .star {
        animation: none;
    }
}
</style>
