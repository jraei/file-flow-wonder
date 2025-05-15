
<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import FlashsaleCard from "./FlashsaleCard.vue";
import FlashsaleHeader from "./FlashsaleHeader.vue";

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
const containerRef = ref(null);

// Calculate remaining time based on server time sync
const endTime = computed(() => {
    return new Date(props.event.event_end_date).getTime();
});

// Check if we're on a large screen (lg+)
const isLargeScreen = computed(() => window.innerWidth >= 1024);

// Determine if carousel should auto-scroll
const shouldAutoScroll = computed(() => {
    return (
        isLargeScreen.value &&
        !isHovering.value &&
        !isUserScrolling.value &&
        isVisible.value
    );
});

// Create a boolean to determine if we should clone items for infinite scrolling
const hasClonedItems = computed(() => {
    return isLargeScreen.value && props.event?.item?.length > 0;
});

// Get the number of items to display based on viewport
const visibleItems = computed(() => {
    if (window.innerWidth >= 1280) return 4; // xl
    if (window.innerWidth >= 1024) return 3; // lg
    if (window.innerWidth >= 768) return 2; // md
    return 1; // sm and xs
});

// Calculate the number of items to clone
const cloneCount = computed(() => {
    return Math.min(props.event?.item?.length || 0, visibleItems.value);
});

// Handle manual scrolling with improved detection
const handleScroll = () => {
    if (!carouselRef.value) return;
    isUserScrolling.value = true;

    // Handle infinite scroll effect for large screens
    if (isLargeScreen.value) {
        const carousel = carouselRef.value;
        const scrollWidth = carousel.scrollWidth;
        const containerWidth = carousel.clientWidth;
        
        // If near end, jump to start
        if (carousel.scrollLeft > scrollWidth - containerWidth - 50) {
            // Add small delay before jumping to make transition smoother
            setTimeout(() => {
                carousel.scrollTo({
                    left: 1, // Small offset to prevent jump
                    behavior: 'auto'
                });
            }, 50);
        }
        
        // If near start (after a backward scroll), jump to end
        else if (carousel.scrollLeft < 50) {
            const originalItems = props.event.item.length;
            const cardWidth = carousel.querySelector('.flex-none')?.clientWidth || 0;
            const spaceBetweenCards = 16; // This is the space-x-4 = 1rem = 16px
            
            // Jump to position just before cloned items
            setTimeout(() => {
                carousel.scrollTo({
                    left: (cardWidth + spaceBetweenCards) * originalItems - containerWidth,
                    behavior: 'auto'
                });
            }, 50);
        }
    }

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
    
    // Reset any auto-scroll related settings when view changes
    if (carouselRef.value && !isMobile.value) {
        // Re-center carousel after resize
        const firstCard = carouselRef.value.querySelector('.flex-none');
        if (firstCard) {
            carouselRef.value.scrollLeft = 0;
        }
    }
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

// Scroll controls for button navigation
const scrollCarousel = (direction) => {
    if (!carouselRef.value) return;
    
    const carousel = carouselRef.value;
    const cardWidth = carousel.querySelector('.flex-none')?.clientWidth || 0;
    const spaceBetweenCards = 16; // space-x-4 = 1rem = 16px
    
    // Calculate distance to scroll (one full card width + margin)
    const scrollDistance = cardWidth + spaceBetweenCards;
    
    carousel.scrollBy({
        left: direction === 'left' ? -scrollDistance : scrollDistance,
        behavior: 'smooth'
    });
    
    // Mark as user scrolling to pause auto-scroll
    isUserScrolling.value = true;
    if (scrollTimeoutId.value) {
        window.clearTimeout(scrollTimeoutId.value);
    }
    
    // Reset after animation completes
    scrollTimeoutId.value = window.setTimeout(() => {
        isUserScrolling.value = false;
    }, 1000);
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
        <div
            ref="containerRef"
            class="relative z-10 p-4 mx-auto max-w-7xl backdrop-blur rounded-2xl"
        >
            <!-- Flash Sale Header -->
            <FlashsaleHeader
                :event-name="event.event_name"
                :end-time="endTime"
                :server-time="serverTime"
            />

            <!-- Enhanced Cards Carousel with Navigation -->
            <div class="relative flashsale-carousel">
                <!-- Navigation buttons for larger screens -->
                <button 
                    @click="scrollCarousel('left')" 
                    class="hidden md:flex absolute top-1/2 left-2 -translate-y-1/2 z-20 bg-primary/10 hover:bg-primary/20 text-white w-8 h-8 rounded-full items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <button 
                    @click="scrollCarousel('right')" 
                    class="hidden md:flex absolute top-1/2 right-2 -translate-y-1/2 z-20 bg-primary/10 hover:bg-primary/20 text-white w-8 h-8 rounded-full items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Scroll indicators (fade edges) for visual effect -->
                <div class="scroll-fade-left"></div>
                <div class="scroll-fade-right"></div>

                <!-- Main carousel container with optimized scrolling -->
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
                    <!-- Clone items at the beginning for seamless loop (lg screens) -->
                    <template v-if="hasClonedItems">
                        <FlashsaleCard
                            v-for="(item, index) in event.item.slice(-cloneCount)"
                            :key="`pre-clone-${item.id}-${index}`"
                            :flash-item="item"
                            class="flex-none snap-start pre-clone-card"
                            :style="{
                                width: isMobile 
                                    ? 'calc(100% - 2rem)' 
                                    : isLargeScreen 
                                        ? 'calc((100% - 3rem) / 4)'
                                        : 'calc((100% - 1rem) / 2)',
                                minWidth: '290px',
                            }"
                        />
                    </template>

                    <!-- Original flashsale items -->
                    <FlashsaleCard
                        v-for="item in event.item"
                        :key="item.id"
                        :flash-item="item"
                        class="flex-none snap-start"
                        :style="{
                            width: isMobile 
                                ? 'calc(100% - 2rem)' 
                                : isLargeScreen 
                                    ? 'calc((100% - 3rem) / 4)'
                                    : 'calc((100% - 1rem) / 2)',
                            minWidth: '290px',
                        }"
                    />

                    <!-- Clone items at the end for seamless loop (lg screens) -->
                    <template v-if="hasClonedItems">
                        <FlashsaleCard
                            v-for="(item, index) in event.item.slice(0, cloneCount)"
                            :key="`post-clone-${item.id}-${index}`"
                            :flash-item="item"
                            class="flex-none snap-start post-clone-card"
                            :style="{
                                width: isMobile 
                                    ? 'calc(100% - 2rem)' 
                                    : isLargeScreen 
                                        ? 'calc((100% - 3rem) / 4)'
                                        : 'calc((100% - 1rem) / 2)',
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

/* Optimized automatic CSS-based scrolling animation */
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

    /* Prepare cloned cards for smooth transitions */
    .pre-clone-card, .post-clone-card {
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
}

@media (prefers-reduced-motion: reduce) {
    .auto-scroll {
        animation: none;
    }
}
</style>
