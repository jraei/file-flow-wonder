<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import FlashsaleCard from "./FlashsaleCard.vue";
import FlashsaleHeader from "./FlashsaleHeader.vue";
import CssCosmicParticles from "./CssCosmicParticles.vue";

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    serverTime: {
        type: String,
        required: true,
    },
});

const carouselRef = ref(null);
const isHovering = ref(false);
const isMobile = ref(window.innerWidth < 768);
const isUserScrolling = ref(false);
const scrollTimeoutId = ref(null);
const isVisible = ref(false);
const observerRef = ref(null);

// Calculate remaining time based on server time sync
const endTime = computed(() => {
    return new Date(props.event.event_end_date).getTime();
});

// Check if we're on a large screen (lg+)
const isLargeScreen = computed(() => window.innerWidth >= 1024);

// Auto scroll animation state
const shouldAutoScroll = computed(() => {
    return (
        isLargeScreen.value &&
        !isHovering.value &&
        !isUserScrolling.value &&
        isVisible.value
    );
});

// Handle manual scrolling with improved detection
const handleScroll = () => {
    if (!carouselRef.value) return;
    isUserScrolling.value = true;

    // Clear previous timeout
    if (scrollTimeoutId.value) {
        window.clearTimeout(scrollTimeoutId.value);
    }

    // Set new timeout to detect when user stops scrolling
    scrollTimeoutId.value = window.setTimeout(() => {
        isUserScrolling.value = false;
    }, 1000); // Longer delay for better UX
};

// Check viewport size
const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
};

// Clean up all timeouts
const cleanupTimeouts = () => {
    if (scrollTimeoutId.value) {
        window.clearTimeout(scrollTimeoutId.value);
        scrollTimeoutId.value = null;
    }
};

// Use intersection observer to detect when carousel is visible
const setupVisibilityObserver = () => {
    if (!carouselRef.value || typeof IntersectionObserver === "undefined")
        return;

    observerRef.value = new IntersectionObserver(
        (entries) => {
            isVisible.value = entries[0].isIntersecting;
        },
        { threshold: 0.1 }
    );

    observerRef.value.observe(carouselRef.value);
};

onMounted(() => {
    handleResize();
    window.addEventListener("resize", handleResize);
    setupVisibilityObserver();

    // Handle tab visibility
    document.addEventListener("visibilitychange", () => {
        isVisible.value = !document.hidden && isVisible.value;
    });
});

onUnmounted(() => {
    cleanupTimeouts();
    window.removeEventListener("resize", handleResize);

    // Clean up observer
    if (observerRef.value && carouselRef.value) {
        observerRef.value.unobserve(carouselRef.value);
        observerRef.value = null;
    }
});
</script>

<template>
    <section class="relative p-4 py-8 overflow-hidden bg-content_background">
        <!-- CSS-based cosmic particles overlay -->
        <div class="absolute inset-0 z-0">
            <CssCosmicParticles />
        </div>

        <div
            class="relative z-10 p-4 mx-auto max-w-7xl bg-gradient-to-r from-primary/20 to-primary/10 backdrop-blur rounded-2xl"
        >
            <!-- Flash Sale Header -->
            <FlashsaleHeader
                :event-name="event.event_name"
                :end-time="endTime"
                :server-time="serverTime"
            />

            <!-- Enhanced Cards Carousel -->
            <div class="relative flashsale-carousel">
                <!-- Scroll indicators (fade edges) for visual effect -->
                <div class="scroll-fade-left"></div>
                <div class="scroll-fade-right"></div>

                <!-- Main carousel container with CSS auto-scroll -->
                <div
                    ref="carouselRef"
                    @scroll="handleScroll"
                    @mouseenter="isHovering = true"
                    @mouseleave="isHovering = false"
                    @touchstart="isHovering = true"
                    @touchend="
                        () => {
                            window.setTimeout(() => (isHovering = false), 1000);
                        }
                    "
                    class="flex pt-2 pb-4 space-x-4 overflow-x-auto snap-x scrollbar-none"
                    :class="{ 'auto-scroll': shouldAutoScroll }"
                >
                    <FlashsaleCard
                        v-for="item in event.item"
                        :key="item.id"
                        :flash-item="item"
                        class="flex-none snap-start"
                        :style="{
                            width: 'calc((100% - 3rem) / 4)',
                            minWidth: '290px',
                        }"
                    />

                    <!-- Clone first items for smooth infinite scroll effect on large screens -->
                    <template v-if="isLargeScreen">
                        <FlashsaleCard
                            v-for="(item, index) in event.item.slice(0, 4)"
                            :key="`clone-${item.id}`"
                            :flash-item="item"
                            class="flex-none snap-start clone-card"
                            :style="{
                                width: 'calc((100% - 3rem) / 4)',
                                minWidth: '290px',
                            }"
                        />
                    </template>

                    <!-- Spacer element to ensure proper scrolling -->
                    <div class="flex-none w-4"></div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Hide scrollbar but keep functionality */
.scrollbar-none {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

.scrollbar-none::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* Create lightweight CRT scanline effect */
section::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0) 50%,
        rgba(0, 0, 0, 0.05) 50%
    );
    background-size: 100% 4px;
    pointer-events: none;
    z-index: 2;
    opacity: 0.05;
}

/* Scroll fade indicators */
.scroll-fade-left,
.scroll-fade-right {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 60px;
    z-index: 10;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.flashsale-carousel:hover .scroll-fade-left,
.flashsale-carousel:hover .scroll-fade-right {
    opacity: 0.7;
}

.scroll-fade-left {
    left: 0;
    background: linear-gradient(to right, rgba(31, 41, 55, 0.8), transparent);
}

.scroll-fade-right {
    right: 0;
    background: linear-gradient(to left, rgba(31, 41, 55, 0.8), transparent);
}

/* Automatic CSS-based scrolling animation for large screens */
@keyframes autoScroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(calc(-100% / 2));
    }
}

/* Only apply auto-scroll on large screens */
@media (min-width: 1024px) {
    .auto-scroll {
        animation: autoScroll 30s linear infinite;
        animation-play-state: running;
    }

    .auto-scroll:hover {
        animation-play-state: paused;
    }

    /* Hide cloned cards by default */
    .clone-card {
        opacity: 0.01; /* Not fully hidden to keep layout intact */
        transition: opacity 0.5s ease;
    }

    /* Show clones during animation */
    .auto-scroll .clone-card {
        opacity: 1;
    }
}

/* Snap points for mobile scrolling */
@media (max-width: 767px) {
    .snap-x {
        scroll-snap-type: x mandatory;
    }

    .snap-start {
        scroll-snap-align: start;
    }
}

/* Performance optimizations */
.flashsale-carousel {
    backface-visibility: hidden;
    transform: translateZ(0);
    will-change: scroll-position;
}

@media (prefers-reduced-motion: reduce) {
    .auto-scroll {
        animation: none;
    }
}
</style>
