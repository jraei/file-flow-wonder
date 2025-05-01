<template>
    <div class="absolute inset-0 pointer-events-none z-25 comets-container">
        <div
            v-for="(comet, index) in comets"
            :key="`comet-${index}`"
            class="absolute comet"
            :class="[`comet-${index + 1}`]"
            :style="{
                width: `${comet.size}px`,
                height: `${comet.size}px`,
                left: comet.leftToRight ? '-5%' : '105%',
                top: `${comet.top}%`,
                opacity: 0,
                backgroundColor: '#fff',
                boxShadow: comet.glow,
                filter: 'drop-shadow(0 0 3px rgba(255, 255, 255, 0.8))',
                animationName: comet.leftToRight
                    ? 'comet-left-to-right'
                    : 'comet-right-to-left',
                animationDuration: `${comet.duration}s`,
                animationDelay: `${comet.delay}s`,
                animationTimingFunction: 'ease-in-out',
                animationIterationCount: 'infinite',
            }"
        >
            <!-- Dual tail effect (dust + ion) -->
            <div
                class="absolute comet-tail"
                :style="{
                    height: '2px',
                    width: `${comet.tailLength}px`,
                    left: comet.leftToRight ? '-100%' : '100%',
                    top: '50%',
                    transform: 'translateY(-50%)',
                    background: comet.tailGradient,
                }"
            ></div>
            <div
                class="absolute comet-ion-tail"
                :style="{
                    height: '1px',
                    width: `${comet.tailLength * 0.7}px`,
                    left: comet.leftToRight ? '-70%' : '70%',
                    top: '50%',
                    transform: 'translateY(-50%) rotate(5deg)',
                    background: comet.ionTailGradient,
                }"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

const isReducedMotion =
    window?.matchMedia("(prefers-reduced-motion: reduce)")?.matches || false;
const isMobile = computed(() => window?.innerWidth < 768);

// Skip generating comets on mobile devices
const comets = ref([]);

onMounted(() => {
    if (!isMobile.value && !isReducedMotion) {
        generateComets();
        window.addEventListener("resize", generateComets);
    }
});

const generateComets = () => {
    const cometCount = 3; // 3 comets
    const cometData = [];

    // Insert keyframes for comet animations
    const styleSheet = document.styleSheets[0];

    try {
        styleSheet.insertRule(
            `
            @keyframes comet-left-to-right {
                0% { left: -5%; opacity: 0; }
                5% { opacity: 1; }
                95% { opacity: 1; }
                100% { left: 105%; opacity: 0; }
            }
        `,
            styleSheet.cssRules.length
        );

        styleSheet.insertRule(
            `
            @keyframes comet-right-to-left {
                0% { left: 105%; opacity: 0; }
                5% { opacity: 1; }
                95% { opacity: 1; }
                100% { left: -5%; opacity: 0; }
            }
        `,
            styleSheet.cssRules.length
        );
    } catch (e) {
        console.warn("Could not insert CSS rule:", e);
    }

    for (let i = 0; i < cometCount; i++) {
        const size = Math.random() * 3 + 3; // 3-6px core size
        const leftToRight = Math.random() > 0.5; // 50% left-to-right, 50% right-to-left
        const tailLength = Math.random() * 60 + 40; // 40-100px
        const duration = Math.random() * 8 + 15; // 15-23s for full traverse
        const delay = Math.random() * 15 + 15; // 15-30s between appearances

        const tailGradient = `linear-gradient(${
            leftToRight ? "to left" : "to right"
        }, rgba(255,255,255,0.8), rgba(255,220,170,0.4), transparent)`;
        const ionTailGradient = `linear-gradient(${
            leftToRight ? "to left" : "to right"
        }, rgba(155, 135, 245, 0.6), rgba(51, 195, 240, 0.3), transparent)`;

        cometData.push({
            size,
            leftToRight,
            top: Math.random() * 70 + 15, // 15-85% from top
            tailLength,
            duration,
            delay,
            glow: `0 0 ${size * 1.5}px rgba(255,255,255,0.8)`,
            tailGradient,
            ionTailGradient,
        });
    }

    comets.value = cometData;
};
</script>

<style scoped>
.comets-container {
    overflow: hidden;
}

.comet {
    border-radius: 50%;
    z-index: 25;
}

@media (prefers-reduced-motion: reduce) {
    .comets-container {
        display: none;
    }
}
</style>
