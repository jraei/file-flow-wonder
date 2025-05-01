<script setup>
import { computed, ref, onMounted } from "vue";

const props = defineProps({
    size: {
        type: Object,
        default: () => ({
            sm: 80, // Size in px for mobile
            md: 120, // Size in px for tablet
            lg: 160, // Size in px for desktop
        }),
    },
});

// Element ref for measuring
const earthRef = ref(null);

// Compute current size based on viewport
const currentSize = computed(() => {
    if (typeof window === "undefined") return props.size.md;

    const width = window.innerWidth;
    if (width < 640) return props.size.sm;
    if (width < 1024) return props.size.md;
    return props.size.lg;
});

// Calculate shadow size based on current Earth size
const shadowSize = computed(() => {
    return currentSize.value * 1.2; // 20% larger than Earth
});

// Control cloud layer rotation animation
const cloudRotation = ref(0);
let cloudAnimationFrame = null;
let lastTimestamp = 0;

const animateClouds = (timestamp) => {
    if (!lastTimestamp) lastTimestamp = timestamp;
    const elapsed = timestamp - lastTimestamp;

    // Complete rotation in 2 minutes (120000ms)
    // Calculate degrees to rotate based on elapsed time
    const rotationSpeed = 360 / 120000; // degrees per ms
    cloudRotation.value += rotationSpeed * elapsed;

    // Normalize rotation to 0-360
    if (cloudRotation.value >= 360) {
        cloudRotation.value -= 360;
    }

    lastTimestamp = timestamp;
    cloudAnimationFrame = requestAnimationFrame(animateClouds);
};

// Lifecycle hooks
onMounted(() => {
    // Start cloud animation
    cloudAnimationFrame = requestAnimationFrame(animateClouds);

    // Clean up on unmount
    return () => {
        if (cloudAnimationFrame) {
            cancelAnimationFrame(cloudAnimationFrame);
        }
    };
});

const cloudsvg = `
  <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <rect width="100%" height="100%" fill="none"/>
    <g fill="white">
      <circle cx="20" cy="30" r="10"/>
      <circle cx="30" cy="35" r="8"/>
      <circle cx="25" cy="25" r="5"/>
      <circle cx="70" cy="60" r="15"/>
      <circle cx="80" cy="70" r="10"/>
      <circle cx="60" cy="60" r="12"/>
      <circle cx="50" cy="20" r="8"/>
    </g>
  </svg>
`;

const encodedCloudSvg = `url("data:image/svg+xml;utf8,${encodeURIComponent(
    cloudsvg
)}")`;

const landSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
  <rect width="100%" height="100%" fill="none"/>
  <g fill="green">
    <path d="M30,30 Q40,20 50,30 T70,40 Q80,50 70,60 T50,70 Q30,65 20,50 T30,30 Z"/>
    <path d="M80,20 Q90,30 85,40 T75,50 Q65,45 60,30 T80,20 Z"/>
  </g>
</svg>
`;

const encodedLandSvg = `url("data:image/svg+xml;utf8,${encodeURIComponent(
    landSvg
)}")`;

const landMassStyle = computed(() => ({
    background: encodedLandSvg,
    backgroundSize: "cover",
    mixBlendMode: "soft-light",
}));
</script>

<template>
    <div
        ref="earthRef"
        class="relative inline-block"
        :style="{
            width: `${currentSize}px`,
            height: `${currentSize}px`,
        }"
    >
        <!-- Earth shadow/glow effect -->
        <div
            class="absolute rounded-full bg-secondary/20 filter blur-xl"
            :style="{
                width: `${shadowSize}px`,
                height: `${shadowSize}px`,
                top: `${(currentSize - shadowSize) / 2}px`,
                left: `${(currentSize - shadowSize) / 2}px`,
            }"
        ></div>

        <!-- Earth base sphere -->
        <div
            class="absolute inset-0 rounded-full bg-gradient-to-br from-blue-600 via-blue-500 to-blue-800"
            :style="{
                boxShadow:
                    'inset -20px -20px 40px rgba(0,0,0,0.6), inset 20px 20px 40px rgba(255,255,255,0.3)',
            }"
        ></div>

        <!-- Cloud layer with rotation -->
        <div
            class="absolute inset-0 rounded-full opacity-40"
            :style="{
                background: encodedCloudSvg,
                transform: `rotate(${cloudRotation}deg)`,
                backgroundSize: 'cover',
                mixBlendMode: 'screen',
            }"
        />

        <!-- Land masses -->
        <div
            class="absolute inset-0 rounded-full opacity-80"
            :style="landMassStyle"
        ></div>

        <!-- Highlight reflection -->
        <div
            class="absolute rounded-full bg-white/20"
            :style="{
                width: `${currentSize * 0.15}px`,
                height: `${currentSize * 0.15}px`,
                top: `${currentSize * 0.15}px`,
                left: `${currentSize * 0.15}px`,
                filter: 'blur(5px)',
            }"
        ></div>

        <!-- Orbiting micro-meteors -->
        <div
            v-for="i in 8"
            :key="`meteor-${i}`"
            class="absolute bg-white rounded-full"
            :style="{
                width: `${2 + Math.random() * 2}px`,
                height: `${1 + Math.random() * 1}px`,
                top: `${
                    currentSize / 2 +
                    Math.sin((i / 8) * Math.PI * 2) * (currentSize / 2 + 10)
                }px`,
                left: `${
                    currentSize / 2 +
                    Math.cos((i / 8) * Math.PI * 2) * (currentSize / 2 + 10)
                }px`,
                boxShadow: '0 0 3px white',
                animation: `orbit-${i} ${10 + i * 3}s linear infinite`,
            }"
        ></div>
    </div>
</template>

<style scoped>
@keyframes orbit-1 {
    from {
        transform: rotate(0deg) translateX(40px) rotate(0deg);
    }
    to {
        transform: rotate(360deg) translateX(40px) rotate(-360deg);
    }
}
@keyframes orbit-2 {
    from {
        transform: rotate(45deg) translateX(50px) rotate(-45deg);
    }
    to {
        transform: rotate(405deg) translateX(50px) rotate(-405deg);
    }
}
@keyframes orbit-3 {
    from {
        transform: rotate(90deg) translateX(45px) rotate(-90deg);
    }
    to {
        transform: rotate(450deg) translateX(45px) rotate(-450deg);
    }
}
@keyframes orbit-4 {
    from {
        transform: rotate(135deg) translateX(55px) rotate(-135deg);
    }
    to {
        transform: rotate(495deg) translateX(55px) rotate(-495deg);
    }
}
@keyframes orbit-5 {
    from {
        transform: rotate(180deg) translateX(48px) rotate(-180deg);
    }
    to {
        transform: rotate(540deg) translateX(48px) rotate(-540deg);
    }
}
@keyframes orbit-6 {
    from {
        transform: rotate(225deg) translateX(52px) rotate(-225deg);
    }
    to {
        transform: rotate(585deg) translateX(52px) rotate(-585deg);
    }
}
@keyframes orbit-7 {
    from {
        transform: rotate(270deg) translateX(42px) rotate(-270deg);
    }
    to {
        transform: rotate(630deg) translateX(42px) rotate(-630deg);
    }
}
@keyframes orbit-8 {
    from {
        transform: rotate(315deg) translateX(60px) rotate(-315deg);
    }
    to {
        transform: rotate(675deg) translateX(60px) rotate(-675deg);
    }
}
</style>
