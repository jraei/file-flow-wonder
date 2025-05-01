<script setup>
import { ref, computed, watch } from "vue";
import CategoryFilter from "./CategoryFilter.vue";
import ProductCatalogItem from "./ProductCatalogItem.vue";
import CosmicParticles from "../Flashsale/CosmicParticles.vue";
import { debounce } from "lodash";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
});

const activeCategory = ref(null);

// Filtered products based on selected category
const filteredProducts = computed(() => {
    if (activeCategory.value === null) {
        return props.products;
    }
    return props.products.filter(
        (product) => product.kategori_id === activeCategory.value
    );
});

// Debounced category selection to prevent rapid UI updates
const selectCategory = debounce((categoryId) => {
    activeCategory.value = categoryId;
}, 150);

// Reset category when categories or products change
watch(
    () => props.categories,
    () => {
        activeCategory.value = null;
    },
    { deep: true }
);

watch(
    () => props.products,
    () => {
        activeCategory.value = null;
    },
    { deep: true }
);
</script>

<template>
    <section
        class="relative w-full pt-12 pb-48 overflow-hidden bg-content_background"
    >
        <!-- Cosmic Background -->
        <div class="absolute inset-0 z-0">
            <CosmicParticles />

            <!-- Interstellar Dust Clouds -->
            <div
                class="absolute inset-x-0 top-32 h-96 opacity-20 -rotate-12 bg-gradient-radial from-primary/20 via-transparent to-transparent"
            ></div>
            <div
                class="absolute inset-x-0 bottom-48 h-96 opacity-20 rotate-12 bg-gradient-radial from-secondary/20 via-transparent to-transparent"
            ></div>
        </div>

        <div class="relative z-10 px-4 mx-auto max-w-7xl">
            <!-- Section Header -->
            <div class="mb-8">
                <div class="flex items-center mb-3 space-x-2">
                    <div
                        class="flex items-center justify-center w-10 h-10 border rounded-full rocket-container border-primary/30 bg-primary/10"
                    >
                        <span class="text-lg">🚀</span>
                    </div>
                    <h2
                        class="text-2xl font-bold md:text-2xl text-primary-text drop-shadow-glow"
                    >
                        Product Catalog
                    </h2>
                </div>

                <!-- Category Filter -->
                <div class="mt-6">
                    <CategoryFilter
                        :categories="categories"
                        :activeCategory="activeCategory"
                        @selectCategory="selectCategory"
                    />
                </div>
            </div>

            <!-- Product Grid -->
            <div
                class="grid grid-cols-3 gap-6 mt-8 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6"
            >
                <ProductCatalogItem
                    v-for="(product, index) in filteredProducts"
                    :key="product.id"
                    :product="product"
                    :index="index"
                    class="hover:cursor-pointer"
                    @click="router.visit(route('order.index', product.slug))"
                />
            </div>

            <!-- Empty State -->
            <div v-if="filteredProducts.length === 0" class="py-16 text-center">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-secondary/20"
                >
                    <span class="text-2xl">🔍</span>
                </div>
                <h3 class="text-xl font-medium text-primary-text">
                    No products found
                </h3>
                <p class="mt-2 text-primary-text/70">
                    Try selecting a different category
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.drop-shadow-glow {
    filter: drop-shadow(0 0 6px rgba(155, 135, 245, 0.6));
}

.rocket-container {
    animation: pulse-glow 2s infinite alternate;
}

@keyframes pulse-glow {
    0% {
        box-shadow: 0 0 5px rgba(155, 135, 245, 0.3);
    }
    100% {
        box-shadow: 0 0 15px rgba(155, 135, 245, 0.7);
    }
}
</style>
