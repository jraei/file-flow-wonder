<script setup>
import { router } from "@inertiajs/vue3";
import ProductCard from "./ProductCard.vue";
import CosmicParticles from "../Flashsale/CosmicParticles.vue";

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section class="relative w-full py-8 overflow-hidden bg-content_background">
        <div class="absolute inset-0 z-0">
            <CosmicParticles />
        </div>

        <div class="relative z-10 px-4 mx-auto max-w-7xl">
            <!-- Section Header -->
            <div class="mb-8">
                <div class="flex items-center mb-2 space-x-2">
                    <div class="fire-icon-container">
                        <span class="fire-icon">🔥</span>
                    </div>
                    <h2
                        class="text-2xl font-bold md:text-2xl text-primary-text drop-shadow-glow"
                    >
                        Populer Sekarang
                    </h2>
                </div>
                <p
                    class="text-sm md:text-sm text-primary-text/80 ml-14 animate-fade-in"
                >
                    Berikut adalah beberapa produk yang paling populer saat ini.
                </p>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5">
                <ProductCard
                    v-for="(product, index) in products"
                    :key="product.id"
                    :product="product"
                    :index="index"
                    class="h-[100px] md:h-[130px] hover:cursor-pointer"
                    @click="router.visit(route('order.index', product.slug))"
                />
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

.drop-shadow-glow {
    filter: drop-shadow(0 0 6px rgba(155, 135, 245, 0.6));
}

.fire-icon-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(155, 135, 245, 0.15);
    border-radius: 50%;
    animation: pulse-glow 2s infinite alternate;
    border: 1px solid rgba(155, 135, 245, 0.3);
}

.fire-icon {
    width: 24px;
    height: 24px;
    color: #33c3f0; /* secondary color */
    filter: drop-shadow(0 0 5px rgba(51, 195, 240, 0.7));
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
