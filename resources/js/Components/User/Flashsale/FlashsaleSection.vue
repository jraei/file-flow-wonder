<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import FlashsaleCard from "./FlashsaleCard.vue";
import FlashsaleHeader from "./FlashsaleHeader.vue";
import CosmicParticles from "./CosmicParticles.vue";

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
const isScrolling = ref(false);
const isHovering = ref(false);
const isMobile = ref(window.innerWidth < 768);
const isUserScrolling = ref(false);
const scrollTimeoutId = ref(null);
const scrollAnimationId = ref(null);
const isCloned = ref(false);

// Calculate remaining time based on server time sync
const endTime = computed(() => {
    return new Date(props.event.event_end_date).getTime();
});

// Calculate scroll speed based on device size
const getScrollSpeed = () => {
    return isMobile.value ? 0 : 2.5; // 2.5px/sec on desktop only
};

// Clone cards if needed for infinite scrolling
const ensureInfiniteScroll = () => {
    if (!carouselRef.value || isCloned.value) return;

    const container = carouselRef.value;
    const items = container.querySelectorAll(".flashsale-card");

    // Only clone if we have less than 8 items
    if (items.length < 8) {
        isCloned.value = true;
        const fragment = document.createDocumentFragment();

        // Clone each card
        items.forEach((item) => {
            const clone = item.cloneNode(true);
            clone.classList.add("cloned-card");
            fragment.appendChild(clone);
        });

        // Add clones at the end
        container.appendChild(fragment);
    }
};

// Use requestAnimationFrame for smoother animation
let lastTimestamp = 0;
const animate = (timestamp) => {
    if (isHovering.value || isUserScrolling.value || !carouselRef.value) {
        scrollAnimationId.value = requestAnimationFrame(animate);
        return;
    }

    // Control frame rate for performance
    const elapsed = timestamp - lastTimestamp;
    if (elapsed > 16) {
        // ~60fps
        lastTimestamp = timestamp;

        // Increment scroll position based on speed
        carouselRef.value.scrollLeft += getScrollSpeed();

        // Check if we need to loop back
        const container = carouselRef.value;
        const scrollRight = container.scrollWidth - container.clientWidth;

        // When we reach the end (with 20px buffer), reset seamlessly
        if (container.scrollLeft >= scrollRight - 20) {
            resetScroll();
        }
    }

    scrollAnimationId.value = requestAnimationFrame(animate);
};

// Handle seamless reset with animation
const resetScroll = () => {
    if (!carouselRef.value) return;

    const container = carouselRef.value;
    isScrolling.value = true;

    // Cancel any existing animation
    if (scrollAnimationId.value) {
        cancelAnimationFrame(scrollAnimationId.value);
    }

    // Animate scroll to beginning with easing
    const startPosition = container.scrollLeft;
    const startTime = performance.now();
    const duration = 800; // ms

    const animateScroll = (timestamp) => {
        const elapsed = timestamp - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Cubic ease out for smooth deceleration
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        container.scrollLeft = startPosition * (1 - easeProgress);

        if (progress < 1) {
            scrollAnimationId.value = requestAnimationFrame(animateScroll);
        } else {
            container.scrollLeft = 0;
            isScrolling.value = false;
            scrollAnimationId.value = requestAnimationFrame(animate);
        }
    };

    scrollAnimationId.value = requestAnimationFrame(animateScroll);
};

// Handle manual scrolling with improved detection
const handleScroll = () => {
    if (!carouselRef.value) return;

    isUserScrolling.value = true;

    // Clear previous timeout and use requestAnimationFrame for better performance
    if (scrollTimeoutId.value) {
        window.clearTimeout(scrollTimeoutId.value);
    }

    // Set new timeout to detect when user stops scrolling
    scrollTimeoutId.value = window.setTimeout(() => {
        isUserScrolling.value = false;
    }, 250);
};

// Check if we're on mobile/tablet
const handleResize = () => {
    isMobile.value = window.innerWidth < 768;

    // Stop auto-scroll on mobile
    if (isMobile.value && scrollAnimationId.value) {
        cancelAnimationFrame(scrollAnimationId.value);
        scrollAnimationId.value = null;
    } else if (!isMobile.value && !scrollAnimationId.value) {
        // Restart on desktop
        ensureInfiniteScroll();
        lastTimestamp = performance.now();
        scrollAnimationId.value = requestAnimationFrame(animate);
    }
};

// Clean up all animations
const cleanupAnimations = () => {
    if (scrollAnimationId.value) {
        cancelAnimationFrame(scrollAnimationId.value);
        scrollAnimationId.value = null;
    }

    if (scrollTimeoutId.value) {
        window.clearTimeout(scrollTimeoutId.value);
        scrollTimeoutId.value = null;
    }
};

// Implement performance monitoring
const setupPerformanceMonitor = () => {
    if (process.env.NODE_ENV !== "production") {
        let lastTimestamp = performance.now();
        let frameCount = 0;

        const checkPerformance = () => {
            frameCount++;
            const now = performance.now();
            const elapsed = now - lastTimestamp;

            if (elapsed >= 1000) {
                // Check every second
                const fps = Math.round((frameCount * 1000) / elapsed);
                frameCount = 0;
                lastTimestamp = now;

                // Console warning for low FPS
                if (fps < 50 && !isMobile.value) {
                    console.warn(
                        `Flashsale carousel performance: ${fps}fps (target: 60fps)`
                    );
                }
            }

            performanceMonitorId = requestAnimationFrame(checkPerformance);
        };

        let performanceMonitorId = requestAnimationFrame(checkPerformance);

        return () => {
            if (performanceMonitorId) {
                cancelAnimationFrame(performanceMonitorId);
            }
        };
    }

    return () => {};
};

let performanceCleanup;

onMounted(() => {
    // Clone items for infinite scroll
    ensureInfiniteScroll();

    // Only start auto-scroll on desktop
    if (!isMobile.value) {
        lastTimestamp = performance.now();
        scrollAnimationId.value = requestAnimationFrame(animate);
    }

    window.addEventListener("resize", handleResize);
    document.addEventListener("visibilitychange", () => {
        if (document.hidden && scrollAnimationId.value) {
            cancelAnimationFrame(scrollAnimationId.value);
            scrollAnimationId.value = null;
        } else if (
            !document.hidden &&
            !isMobile.value &&
            !scrollAnimationId.value
        ) {
            lastTimestamp = performance.now();
            scrollAnimationId.value = requestAnimationFrame(animate);
        }
    });

    // Setup performance monitoring
    performanceCleanup = setupPerformanceMonitor();
});

onUnmounted(() => {
    cleanupAnimations();
    window.removeEventListener("resize", handleResize);

    if (performanceCleanup) {
        performanceCleanup();
    }
});
</script>

<template>
    <section class="relative p-4 py-8 overflow-hidden bg-content_background">
        <!-- Cosmic particles overlay -->
        <div class="absolute inset-0 z-0">
            <CosmicParticles
                :item-id="'flashsale-section'"
                class="absolute inset-0"
            />
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

            <!-- Enhanced Cards Carousel with improved scroll behavior -->
            <div class="relative flashsale-carousel">
                <!-- Scroll indicators (fade edges) -->
                <!-- <div class="scroll-fade-left"></div>
                <div class="scroll-fade-right"></div> -->

                <!-- Main carousel container -->
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
                    class="flex pt-2 pb-4 space-x-4 overflow-x-auto snap-x scrollbar-none will-change-transform"
                    style="transform: translateZ(0)"
                >
                    <FlashsaleCard
                        v-for="item in event.item"
                        :key="item.id"
                        :flash-item="item"
                        class="flex-none snap-start transform-gpu"
                        :class="{ 'card-breathing': isHovering }"
                        :style="{
                            width: 'calc((100% - 3rem) / 4)',
                            minWidth: '290px',
                        }"
                    />

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

/* Create CRT scanline effect */
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

/* Hardware acceleration for smooth scrolling */
.flashsale-carousel .flex-none {
    will-change: transform;
    transform: translateZ(0);
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

/* Card breathing effect when carousel is paused */
@keyframes card-breathing {
    0%,
    100% {
        transform: scale(1) translateZ(0);
    }
    50% {
        transform: scale(1.02) translateZ(0);
    }
}

.card-breathing {
    animation: card-breathing 4s infinite ease-in-out;
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

/* Hide cloned cards when they're not needed */
@media (max-width: 1200px) {
    .cloned-card {
        display: none;
    }
}
</style>
