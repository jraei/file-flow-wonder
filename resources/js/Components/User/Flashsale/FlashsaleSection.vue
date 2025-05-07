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
const isHovering = ref(false);

// Calculate remaining time based on server time sync
const endTime = computed(() => {
    return new Date(props.event.event_end_date).getTime();
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
