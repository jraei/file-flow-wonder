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

// Compute product details
const productName = computed(() => props.product.nama || "Unnamed Product");
const developer = computed(
    () => props.product.developer || "Unknown Developer"
);
const categoryName = computed(
    () => props.product.kategori?.kategori_name || "Uncategorized"
);
const thumbnail = computed(() => {
    if (props.product.thumbnail && props.product.thumbnail.startsWith("http")) {
        return props.product.thumbnail;
    } else if (props.product.thumbnail) {
        return `/storage/${props.product.thumbnail}`;
    }
    return "/img/default-product.png";
});

// Cosmic visual elements
const planetType = ref(Math.floor(Math.random() * 8));
const meteorCount = ref(Math.floor(Math.random() * 3) + 2);
const meteors = ref([]);

// Generate random meteors
const generateMeteors = () => {
    meteors.value = [];
    for (let i = 0; i < meteorCount.value; i++) {
        meteors.value.push({
            size: Math.random() * 2 + 1,
            duration: Math.random() * 2 + 2,
            delay: Math.random() * 4,
            x: Math.random() * 100,
            y: Math.random() * 100,
            angle: Math.random() * 45 + 20,
        });
    }
};

// Generate planet properties
const planetProperties = computed(() => {
    const planets = [
        {
            color: "bg-blue-500",
            ringColor: "border-blue-300",
            hasRing: false,
            className: "earth",
        },
        {
            color: "bg-red-500",
            ringColor: "border-red-300",
            hasRing: false,
            className: "mars",
        },
        {
            color: "bg-amber-500",
            ringColor: "border-amber-300",
            hasRing: true,
            className: "saturn",
        },
        {
            color: "bg-yellow-500",
            ringColor: "border-yellow-200",
            hasRing: false,
            className: "venus",
        },
        {
            color: "bg-orange-400",
            ringColor: "border-orange-300",
            hasRing: false,
            className: "jupiter",
        },
        {
            color: "bg-cyan-500",
            ringColor: "border-cyan-300",
            hasRing: false,
            className: "neptune",
        },
        {
            color: "bg-indigo-700",
            ringColor: "border-indigo-400",
            hasRing: false,
            className: "uranus",
        },
        {
            color: "bg-gray-400",
            ringColor: "border-gray-300",
            hasRing: false,
            className: "moon",
        },
    ];

    return planets[planetType.value];
});

onMounted(() => {
    generateMeteors();
});

// Check for reduced motion preference
const prefersReducedMotion = ref(false);
const checkReducedMotion = () => {
    prefersReducedMotion.value = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;
};

onMounted(() => {
    checkReducedMotion();
});
</script>

<template>
    <div
        class="relative overflow-hidden transition-transform duration-300 aspect-[2/3] rounded-2xl group hover:scale-[1.03]"
        :class="{ 'reduced-motion': prefersReducedMotion }"
    >
        <!-- Lazy-loaded Product Image -->
        <div class="absolute inset-0 bg-dark-card/10"></div>
        <img
            :src="thumbnail"
            :alt="productName"
            loading="lazy"
            class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110"
        />

        <!-- Hover Gradient Overlay -->
        <div
            class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-dark/90 via-dark/50 to-transparent group-hover:opacity-100"
        ></div>

        <!-- Product Info (visible on hover) -->
        <div
            class="absolute inset-x-0 bottom-0 p-3 transition-transform duration-300 translate-y-full group-hover:translate-y-0"
        >
            <h3 class="text-sm font-bold text-primary-text line-clamp-2">
                {{ productName }}
            </h3>
            <p class="mt-1 text-xs text-primary-text/80">{{ developer }}</p>
            <div
                class="inline-flex px-2 py-1 mt-1 text-xs rounded-full bg-secondary/20 text-primary-text/90"
            >
                {{ categoryName }}
            </div>
        </div>

        <!-- Cosmic Elements -->
        <!-- Planet in corner -->
        <div
            class="absolute transition-opacity -bottom-6 -right-6 w-14 h-14 opacity-60 group-hover:opacity-90"
            :class="{ 'animate-pulse-slow': !prefersReducedMotion }"
        >
            <div
                class="absolute inset-0 rounded-full shadow-[0_0_8px_2px_rgba(155,135,245,0.5)]"
                :class="[planetProperties.color]"
            ></div>

            <!-- Planet Ring (Saturn) -->
            <div
                v-if="planetProperties.hasRing"
                class="absolute border-2 rounded-full inset-2 -rotate-12"
                :class="[planetProperties.ringColor]"
            ></div>

            <!-- Planet Surface Details -->
            <div
                v-if="planetProperties.className === 'jupiter'"
                class="absolute scale-y-50 rounded-full inset-2 bg-orange-300/30 rotate-12"
            ></div>
            <div
                v-if="planetProperties.className === 'mars'"
                class="absolute rotate-45 rounded-full inset-3 bg-red-700/40"
            ></div>
        </div>

        <!-- Meteors -->
        <div
            v-if="!prefersReducedMotion"
            v-for="(meteor, i) in meteors"
            :key="i"
            class="absolute w-8 h-px bg-gradient-to-r from-transparent via-secondary to-transparent meteor"
            :style="{
                top: `${meteor.y}%`,
                left: `${meteor.x - 15}%`,
                transform: `rotate(${meteor.angle}deg)`,
                animationDuration: `${meteor.duration}s`,
                animationDelay: `${meteor.delay}s`,
            }"
        ></div>

        <!-- Border Highlight -->
        <div
            class="absolute inset-0 transition-opacity duration-300 border-2 border-transparent rounded-lg opacity-0 group-hover:border-primary group-hover:opacity-100"
        ></div>
    </div>
</template>

<style scoped>
.meteor {
    animation: meteor-fly 3s linear infinite;
    opacity: 0;
}

@keyframes meteor-fly {
    0% {
        opacity: 0;
        transform: translateX(-100%) translateY(-100%) rotate(45deg);
    }
    10%,
    90% {
        opacity: 1;
    }
    100% {
        opacity: 0;
        transform: translateX(100%) translateY(100%) rotate(45deg);
    }
}

.reduced-motion .meteor {
    animation: none !important;
}
</style>
