
<script setup>
import { ref, onMounted } from "vue";

defineProps({
    banner: String,
});

const isLoaded = ref(false);
const handleImageLoad = () => {
    isLoaded.value = true;
};

onMounted(() => {
    // Check if the image is already cached
    const img = document.querySelector('.cosmic-banner');
    if (img && img.complete) {
        isLoaded.value = true;
    }
});
</script>

<template>
    <div class="relative z-10 mb-16 md:mb-28" v-if="banner">
        <!-- Loading placeholder -->
        <div 
            v-if="!isLoaded" 
            class="object-cover object-center w-full min-h-56 bg-primary/10 animate-pulse flex items-center justify-center lg:min-h-[280px]"
        >
            <div class="w-12 h-12 border-4 border-secondary/30 border-t-secondary rounded-full animate-spin"></div>
        </div>
        
        <img
            :src="`/storage/${banner}`"
            alt="Product Banner"
            class="object-cover object-center w-full h-full min-h-56 bg-muted lg:object-contain cosmic-banner"
            width="1280"
            height="1280"
            loading="lazy"
            @load="handleImageLoad"
            :class="{'opacity-0': !isLoaded, 'opacity-100 transition-opacity duration-500': isLoaded}"
        />
    </div>
</template>

<style scoped>
.cosmic-banner {
    position: relative;
    z-index: 1; /* Ensure it's below other elements that need to overlap */
}

@media (min-width: 1024px) {
    .cosmic-banner {
        width: 100vw;
        margin-left: calc(-50vw + 50%);
    }
}
</style>
