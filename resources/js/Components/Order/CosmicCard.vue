
<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    stepNumber: {
        type: Number,
        default: null,
    },
    totalSteps: {
        type: Number,
        default: 6,
    },
});

// SVG paths for planet icons
const planetIcons = [
    '<svg viewBox="0 0 24 24" class="cosmic-planet"><circle cx="12" cy="12" r="10" stroke="currentColor" fill="none"/></svg>',
    '<svg viewBox="0 0 24 24" class="cosmic-planet"><circle cx="12" cy="12" r="8" stroke="currentColor" fill="none"/><circle cx="12" cy="12" r="4" stroke="currentColor" fill="none"/></svg>',
    '<svg viewBox="0 0 24 24" class="cosmic-planet"><circle cx="12" cy="12" r="8" stroke="currentColor" fill="none"/><ellipse cx="12" cy="12" rx="10" ry="5" stroke="currentColor" fill="none" transform="rotate(30 12 12)"/></svg>',
    '<svg viewBox="0 0 24 24" class="cosmic-planet"><circle cx="12" cy="12" r="8" stroke="currentColor" fill="none"/><circle cx="18" cy="8" r="2" stroke="currentColor" fill="none"/></svg>',
    '<svg viewBox="0 0 24 24" class="cosmic-planet"><circle cx="12" cy="12" r="8" stroke="currentColor" fill="none"/><ellipse cx="12" cy="12" rx="12" ry="3" stroke="currentColor" fill="none"/></svg>',
    '<svg viewBox="0 0 24 24" class="cosmic-planet"><circle cx="12" cy="12" r="10" stroke="currentColor" fill="none"/><circle cx="12" cy="12" r="3" stroke="currentColor" fill="currentColor" opacity="0.3"/></svg>',
];

// Get SVG for the current step
const getPlanetSvg = (step) => {
    if (step === null || step <= 0 || step > planetIcons.length) {
        return '';
    }
    return planetIcons[step - 1];
};
</script>

<template>
    <!-- Cosmic Card Component -->
    <div class="mb-8 overflow-hidden rounded-2xl cosmic-card">
        <!-- Card Header -->
        <div class="relative p-4 text-white bg-primary/20">
            <!-- Constellation pattern -->
            <div class="absolute inset-0 constellation-pattern"></div>

            <div class="relative z-10 flex items-center">
                <!-- Planet Step Indicator -->
                <div v-if="stepNumber !== null" class="mr-3 space-x-1">
                    <div 
                        v-html="getPlanetSvg(stepNumber)"
                        class="w-5 h-5 md:w-6 md:h-6 text-secondary inline-block"
                        :class="{'current-step': true}"
                    ></div>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold md:text-xl">
                        <template v-if="stepNumber !== null">
                            Step {{ stepNumber }} of {{ totalSteps }}:
                        </template>
                        {{ title }}
                    </h3>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div
            class="relative p-6 border bg-gradient-to-b from-primary/20 to-primary/5 border-secondary/20"
        >
            <!-- Micro-planet border accents -->
            <div
                v-for="i in 6"
                :key="`planet-${i}`"
                class="absolute w-1.5 h-1.5 rounded-full bg-secondary/50 micro-planet"
                :style="{
                    top: `${(i % 3) * 33 + 5}%`,
                    left: i <= 3 ? '3px' : 'auto',
                    right: i > 3 ? '3px' : 'auto',
                    animationDelay: `${i * 0.7}s`,
                }"
            ></div>

            <div class="relative z-10">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cosmic-card {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    will-change: transform, box-shadow;
    border: 1px solid transparent;
    background-clip: padding-box;
    position: relative;
}

.cosmic-card::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    margin: -1px;
    border-radius: inherit;
    background: linear-gradient(
        120deg,
        rgba(51, 195, 240, 0.1),
        rgba(155, 135, 245, 0.3)
    );
    animation: border-pulse 4s ease-in-out infinite;
}

.cosmic-card:hover {
    box-shadow: 0 6px 24px rgba(155, 135, 245, 0.2);
}

.constellation-pattern {
    background-image: radial-gradient(
        circle,
        rgba(155, 135, 245, 0.2) 1px,
        transparent 1px
    );
    background-size: 25px 25px;
}

.micro-planet {
    transform: scale(0.8);
    animation: planet-pulse 3s ease-in-out infinite;
}

.cosmic-planet {
    display: inline-block;
    animation: planet-orbit 120s linear infinite;
}

.current-step {
    animation: step-pulse 1.2s ease-in-out infinite;
}

@keyframes border-pulse {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes planet-pulse {
    0%,
    100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
}

@keyframes planet-orbit {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes step-pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.8;
    }
    50% {
        transform: scale(1.1);
        opacity: 1;
        filter: drop-shadow(0 0 3px rgba(51, 195, 240, 0.6));
    }
}
</style>
