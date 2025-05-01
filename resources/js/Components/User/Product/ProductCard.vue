<script setup>
import { computed, ref, onMounted } from "vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        default: 0,
    },
});

const productName = computed(() => props.product.nama || "Unnamed Product");
const developer = computed(
    () => props.product.developer || "Unknown Developer"
);
const thumbnail = computed(() => {
    if (props.product.thumbnail && props.product.thumbnail.startsWith("http")) {
        return props.product.thumbnail;
    } else if (props.product.thumbnail) {
        return `/storage/${props.product.thumbnail}`;
    }
    return "/img/default-product.png";
});

// Cosmos effects configuration
const prefersReducedMotion = ref(false);
const cardVariant = ref(Math.floor(Math.random() * 4));
const starCount = ref(
    prefersReducedMotion.value ? 3 : Math.floor(Math.random() * 3) + 5
);
const galaxyRotation = ref(Math.floor(Math.random() * 360));
const planetType = ref(Math.floor(Math.random() * 4));

// Generate stars
const stars = ref([]);

const generateStars = () => {
    stars.value = [];
    const count = prefersReducedMotion.value ? 3 : starCount.value;

    for (let i = 0; i < count; i++) {
        stars.value.push({
            size: Math.random() * 1.5 + 0.5,
            left: `${Math.random() * 100}%`,
            top: `${Math.random() * 100}%`,
            delay: Math.random() * 3,
            opacity: Math.random() * 0.5 + 0.5,
        });
    }
};

// Detect device capabilities
const checkDevicePreferences = () => {
    // Check for reduced motion preference
    prefersReducedMotion.value = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;

    // Check for low-power device
    const isLowPower = window.navigator.hardwareConcurrency
        ? window.navigator.hardwareConcurrency < 4
        : window.innerWidth < 768;

    if (isLowPower) {
        starCount.value = 3;
    }
};

onMounted(() => {
    checkDevicePreferences();
    generateStars();
});

// Style computations for cosmic elements
const nebulaGradient = computed(() => {
    const gradients = [
        "radial-gradient(circle at 70% 30%, rgba(155, 135, 245, 0.2) 0%, transparent 60%)",
        "radial-gradient(circle at 30% 60%, rgba(51, 195, 240, 0.2) 0%, transparent 65%)",
        "radial-gradient(ellipse at 20% 20%, rgba(155, 135, 245, 0.3) 0%, transparent 70%)",
        "radial-gradient(circle at 80% 30%, rgba(51, 195, 240, 0.3) 0%, transparent 75%)",
    ];
    return gradients[cardVariant.value % gradients.length];
});

const planetStyle = computed(() => {
    const variants = [
        "radial-gradient(circle, #9b87f5 30%, #784af2 100%)", // Purple planet
        "radial-gradient(circle, #33C3F0 20%, #207fa1 100%)", // Blue planet
        "radial-gradient(circle, #6b7280 30%, #4b5563 100%)", // Gray planet
        "radial-gradient(circle, #da8ee7 20%, #8946a6 100%)", // Pink planet
    ];

    return {
        background: variants[planetType.value % variants.length],
    };
});

const hasRing = computed(() => {
    return [0, 2].includes(planetType.value);
});
</script>

<template>
    <div
        class="relative overflow-hidden transition-all duration-300 ease-out border rounded-2xl bg-gradient-to-br from-content_background to-primary/10 border-secondary/30 hover:border-primary cosmic-card group"
        :class="{ 'reduced-motion': prefersReducedMotion }"
    >
        <!-- Nebula Background -->
        <div
            class="absolute inset-0 nebula-background"
            :style="{ backgroundImage: nebulaGradient }"
        ></div>

        <!-- Starfield Pattern -->
        <div class="absolute inset-0 overflow-hidden stars-layer">
            <div
                v-for="(star, i) in stars"
                :key="i"
                class="absolute rounded-full bg-primary-text twinkle-star"
                :style="{
                    width: `${star.size}px`,
                    height: `${star.size}px`,
                    left: star.left,
                    top: star.top,
                    opacity: star.opacity,
                    animationDelay: `${star.delay}s`,
                }"
            ></div>
        </div>

        <!-- Card Body -->
        <div class="flex p-1 h-[90%] relative z-10">
            <!-- Planetary Badge (Bottom Left) -->
            <div class="absolute z-20 top-1 left-2 planet-badge">
                <div class="planet-body" :style="planetStyle"></div>
                <div v-if="hasRing" class="planet-ring"></div>
            </div>
            <!-- Product Thumbnail -->
            <div class="flex items-center justify-center w-2/5 xl:w-1/5">
                <div
                    class="w-16 h-16 overflow-hidden transition-transform duration-500 ease-out border rounded-lg md:w-20 md:h-20 border-primary/30 hover:border-primary/60 product-image-container"
                >
                    <img
                        :src="thumbnail"
                        :alt="productName"
                        loading="lazy"
                        class="object-cover w-full h-full product-image"
                    />
                    <!-- Cosmic Ray Overlay -->
                    <!-- <div class="cosmic-rays"></div> -->
                </div>
            </div>

            <!-- Product Info -->
            <div class="flex flex-col justify-center w-3/5 px-2 xl:w-4/5">
                <div
                    class="p-1 rounded-lg md:rounded-xl md:py-4 backdrop-blur-sm"
                >
                    <h3
                        class="mb-1 text-xs font-bold md:text-base text-primary-text line-clamp-2 product-title"
                    >
                        {{ productName }}
                    </h3>
                    <p
                        class="text-xs md:text-sm text-primary-text/80 product-developer"
                    >
                        {{ developer }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card Footer -->
        <div
            class="border-t border-primary/20 bg-gradient-to-br from-primary/20 to-content_background py-2 px-3 h-[10%] flex items-center justify-end relative z-10"
        >
            <!-- Galaxy Swirl (decorative element in footer) -->
            <div
                class="galaxy-swirl"
                :style="{ transform: `rotate(${galaxyRotation}deg)` }"
            ></div>
        </div>
    </div>
</template>

<style scoped>
/* Base Card Styling */
.cosmic-card {
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    will-change: transform, opacity;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.cosmic-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Nebula Background */
.nebula-background {
    opacity: 0.3;
    transition: opacity 0.4s ease;
    z-index: 1;
    transform: scale(1.2);
    filter: blur(20px);
    will-change: opacity, transform;
}

.cosmic-card:hover .nebula-background {
    opacity: 0.8;
}

/* Stars Layer */
.stars-layer {
    z-index: 2;
}

.twinkle-star {
    position: absolute;
    box-shadow: 0 0 4px rgba(255, 255, 255, 0.8);
    animation: twinkle 3s ease-in-out infinite;
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.2;
        transform: scale(0.8);
    }
    50% {
        opacity: 1;
        transform: scale(1.2);
    }
}

/* Planetary Badge */
.planet-badge {
    position: relative;
    width: 18px;
    height: 18px;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.cosmic-card:hover .planet-badge {
    opacity: 1;
    transform: scale(1.1);
}

.planet-body {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

.planet-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 22px;
    height: 8px;
    margin-left: -11px;
    margin-top: -4px;
    border-radius: 50%;
    border: 1px solid rgba(155, 135, 245, 0.6);
    transform: rotateX(75deg);
}

/* Product Image Container */
.product-image-container {
    position: relative;
    z-index: 15;
    box-shadow: 0 0 10px rgba(155, 135, 245, 0.3);
    transform: translate3d(0, 0, 0);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}

.cosmic-card:hover .product-image-container {
    transform: translate3d(0, -3px, 0) rotate(2deg);
}

/* Product Image */
.product-image {
    transform: scale(1);
    transition: transform 0.8s ease;
}

.cosmic-card:hover .product-image {
    transform: scale(1.05);
}

/* Cosmic Rays */
.cosmic-rays {
    position: absolute;
    inset: 0;
    background-image: linear-gradient(
            45deg,
            transparent 98%,
            rgba(155, 135, 245, 0.3) 98%,
            transparent 100%
        ),
        linear-gradient(
            135deg,
            transparent 97%,
            rgba(51, 195, 240, 0.3) 97%,
            transparent 100%
        );
    opacity: 0.3;
    mix-blend-mode: screen;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.cosmic-card:hover .cosmic-rays {
    opacity: 0.6;
}

/* Info Panel */
.info-panel {
    transition: all 0.2s ease;
    transform: translateZ(0);
    background: rgba(31, 41, 55, 0.6);
}

.cosmic-card:hover .info-panel {
    background: rgba(31, 41, 55, 0.75);
}

/* Text Styles */
.product-title {
    text-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
}

.product-developer {
    text-shadow: 0 0 4px rgba(0, 0, 0, 0.7);
}

/* Galaxy Swirl */
.galaxy-swirl {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-image: conic-gradient(
        from 0deg,
        rgba(155, 135, 245, 0.05),
        rgba(51, 195, 240, 0.3),
        rgba(155, 135, 245, 0.05)
    );
    filter: blur(1px);
    opacity: 0.4;
    transition: opacity 0.3s ease;
    animation: rotate 120s linear infinite;
    transform-origin: center;
}

.cosmic-card:hover .galaxy-swirl {
    opacity: 0.8;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Reduced Motion */
.reduced-motion .twinkle-star,
.reduced-motion .galaxy-swirl,
.reduced-motion:hover .product-image-container,
.reduced-motion:hover .product-image {
    animation: none !important;
    transition: none !important;
    transform: none !important;
}

/* Media Queries for Mobile Optimization */
@media (max-width: 768px) {
    .galaxy-swirl {
        width: 20px;
        height: 20px;
        opacity: 0.3;
    }

    .planet-badge {
        width: 15px;
        height: 15px;
    }

    .planet-ring {
        width: 18px;
        height: 6px;
        margin-left: -9px;
        margin-top: -3px;
    }

    .nebula-background {
        /* Use static background on mobile for better performance */
        animation: none;
        transform: none;
        filter: blur(10px);
    }
}
</style>
