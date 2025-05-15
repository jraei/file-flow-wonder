<template>
    <div
        class="relative z-10 overflow-hidden rounded-2xl"
        :class="{
            'w-[90%] mx-auto': true,
            'md:w-[90%]': true,
            'xl:w-[100%]': true,
        }"
        @mouseenter="pauseAutoplay"
        @mouseleave="resumeAutoplay"
    >
        <!-- Cyberpunk border gradient -->
        <div class="absolute inset-0 p-0.5 rounded-2xl z-0">
            <div
                class="absolute inset-0 rounded-2xl bg-gradient-to-r from-primary/50 to-secondary/50 animate-pulse-slow"
            ></div>
        </div>

        <!-- Carousel container -->
        <div class="relative z-10 overflow-hidden rounded-2xl">
            <div
                class="flex transition-transform duration-800 ease-custom"
                :style="{
                    transform: `translateX(-${currentIndex * 100}%)`,
                    transition: 'transform 800ms cubic-bezier(0.16, 1, 0.3, 1)',
                }"
            >
                <div
                    v-for="banner in bannerImages"
                    :key="banner.id"
                    class="relative h-full min-w-full"
                >
                    <img
                        :src="banner.imageUrl"
                        alt="Banner"
                        class="object-cover w-full h-full"
                        loading="lazy"
                    />
                </div>
            </div>

            <!-- Dot indicators -->
            <div
                class="absolute z-20 flex space-x-2 transform -translate-x-1/2 bottom-3 left-1/2"
            >
                <button
                    v-for="(_, index) in bannerImages"
                    :key="index"
                    @click="goToSlide(index)"
                    class="w-2 h-2 transition-all duration-300 rounded-full"
                    :class="[
                        index === currentIndex
                            ? 'bg-primary w-4 shadow-glow-primary'
                            : 'bg-white/50 hover:bg-white/70',
                    ]"
                ></button>
            </div>

            <!-- Carousel Controls -->
            <Controls @prev="prevSlide" @next="nextSlide" />

            <!-- Hexagonal grid pattern overlay -->
            <!-- <div
                class="absolute inset-0 pointer-events-none hexagon-grid opacity-10"
            ></div> -->

            <!-- CRT Scan Lines Overlay -->
            <!-- <div
                class="absolute inset-0 pointer-events-none crt-scanlines opacity-10"
            ></div> -->
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import Controls from "./Controls.vue";

const props = defineProps({
    banners: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const currentIndex = ref(0);
let autoplayInterval = null;
const AUTOPLAY_DELAY = 5000; // 5 seconds

const bannerImages = computed(() => {
    return props.banners.map((banner) => ({
        id: banner.id,
        imageUrl: banner.image_url || `/storage/${banner.image_path}`,
    }));
});

const totalSlides = computed(() => bannerImages.value.length);

const nextSlide = () => {
    if (totalSlides.value === 0) return;
    currentIndex.value = (currentIndex.value + 1) % totalSlides.value;
};

const prevSlide = () => {
    if (totalSlides.value === 0) return;
    currentIndex.value =
        (currentIndex.value - 1 + totalSlides.value) % totalSlides.value;
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const startAutoplay = () => {
    stopAutoplay();
    if (totalSlides.value > 1) {
        autoplayInterval = setInterval(nextSlide, AUTOPLAY_DELAY);
    }
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
};

const pauseAutoplay = () => {
    stopAutoplay();
};

const resumeAutoplay = () => {
    startAutoplay();
};

// Watch for changes to the banners prop
watch(
    () => props.banners,
    () => {
        if (props.banners.length > 0) {
            startAutoplay();
        }
    },
    { immediate: true }
);

onMounted(() => {
    startAutoplay();
});

onBeforeUnmount(() => {
    stopAutoplay();
});
</script>

<style scoped>
.duration-800 {
    transition-duration: 800ms;
}

.ease-custom {
    transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
