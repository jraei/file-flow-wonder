<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { ArrowLeft, ArrowRight } from "lucide-vue-next";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    activeCategory: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(["selectCategory"]);

const scrollContainer = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);
const isOverflowing = ref(false);

const checkScrollPosition = () => {
    if (!scrollContainer.value) return;
    const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value;

    canScrollLeft.value = scrollLeft > 0;
    canScrollRight.value = scrollLeft + clientWidth < scrollWidth;
    isOverflowing.value = scrollWidth > clientWidth;
};

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const scrollAmount = scrollContainer.value.clientWidth * 0.6;
    scrollContainer.value.scrollTo({
        left:
            scrollContainer.value.scrollLeft +
            (direction === "left" ? -scrollAmount : scrollAmount),
        behavior: "smooth",
    });
};

const selectCategory = (categoryId) => {
    emit("selectCategory", categoryId);
};

// Check for scrollability after render and on window resize
onMounted(() => {
    checkScrollPosition();
    window.addEventListener("resize", checkScrollPosition);

    // Add scroll event listener to update arrow visibility
    scrollContainer.value?.addEventListener("scroll", checkScrollPosition);
});

// Watch for changes to categories array
watch(
    () => props.categories,
    () => {
        setTimeout(checkScrollPosition, 100);
    },
    { deep: true }
);
</script>

<template>
    <div class="relative w-full">
        <!-- Left Arrow -->
        <div
            v-if="isOverflowing && canScrollLeft"
            @click="scroll('left')"
            class="absolute left-0 z-10 flex items-center justify-center h-full cursor-pointer select-none"
        >
            <div
                class="flex items-center justify-center w-8 h-8 transition-all rounded-full bg-primary/20 backdrop-blur-sm hover:bg-primary/40"
            >
                <ArrowLeft class="w-5 h-5 text-primary-text" />
            </div>
        </div>

        <!-- Category Pills Container -->
        <div
            ref="scrollContainer"
            class="flex items-center py-2 space-x-2 overflow-x-auto scrollbar-none"
        >
            <!-- All Categories Option -->
            <div
                @click="selectCategory(null)"
                :class="[
                    activeCategory === null
                        ? 'bg-primary text-primary-text shadow-glow-primary'
                        : 'bg-secondary/20 text-primary-text/80 hover:bg-secondary/30',
                ]"
                class="px-4 py-2 text-sm font-medium transition-all rounded-full cursor-pointer select-none whitespace-nowrap hover:scale-105"
            >
                All Products
            </div>

            <div
                v-for="category in categories"
                :key="category.id"
                @click="selectCategory(category.id)"
                :class="[
                    activeCategory === category.id
                        ? 'bg-primary text-primary-text shadow-glow-primary'
                        : 'bg-secondary/20 text-primary-text/80 hover:bg-secondary/30',
                ]"
                class="px-4 py-2 text-sm font-medium transition-all rounded-full cursor-pointer select-none whitespace-nowrap hover:scale-105"
            >
                {{ category.kategori_name }}
                <span class="ml-1 text-xs opacity-70"
                    >({{ category.produk_count }})</span
                >
            </div>
        </div>

        <!-- Right Arrow -->
        <div
            v-if="isOverflowing && canScrollRight"
            @click="scroll('right')"
            class="absolute right-0 z-10 flex items-center justify-center h-full cursor-pointer select-none"
        >
            <div
                class="flex items-center justify-center w-8 h-8 transition-all rounded-full bg-primary/20 backdrop-blur-sm hover:bg-primary/40"
            >
                <ArrowRight class="w-5 h-5 text-primary-text" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-none {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}

.shadow-glow-primary {
    box-shadow: 0 0 15px rgba(155, 135, 245, 0.5);
}
</style>
