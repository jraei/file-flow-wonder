
<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { debounce } from "lodash";

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

// State variables
const loading = ref(false);
const selectedProductId = ref(null);
const previewData = ref(null);
const error = ref(null);

// Cache for thumbnails to minimize requests
const thumbnailCache = ref({});

// Debounced fetch function
const fetchPreview = debounce(async () => {
    if (!selectedProductId.value) {
        previewData.value = null;
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(route('profit-produks.preview', {
            produk_id: selectedProductId.value
        }));
        
        if (response.data.success) {
            previewData.value = response.data;
            
            // Cache thumbnail
            if (response.data.product && response.data.product.thumbnail) {
                thumbnailCache.value[response.data.product.id] = response.data.product.thumbnail;
            }
        }
    } catch (err) {
        console.error("Error fetching price preview:", err);
        error.value = "Failed to load price preview. Please try again.";
    } finally {
        loading.value = false;
    }
}, 300);

// Watch for product selection changes
watch(() => selectedProductId.value, (newId) => {
    if (newId) {
        fetchPreview();
    } else {
        previewData.value = null;
    }
});

// Find min and max prices for a service to highlight them
const getMinMaxPrices = (rolePrices) => {
    if (!rolePrices || rolePrices.length === 0) return { min: null, max: null };
    
    const prices = rolePrices.map(item => ({
        price: parseFloat(item.final_price),
        roleId: item.role_id
    }));
    
    const min = prices.reduce((prev, curr) => (prev.price < curr.price) ? prev : curr);
    const max = prices.reduce((prev, curr) => (prev.price > curr.price) ? prev : curr);
    
    return { min, max };
};

// Format price with currency
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
};

// Get profit indicator (positive/negative)
const getProfitIndicator = (basePrice, finalPrice) => {
    const diff = finalPrice - basePrice;
    return {
        type: diff >= 0 ? 'positive' : 'negative',
        amount: Math.abs(diff),
        percentage: Math.abs(Math.round((diff / basePrice) * 100))
    };
};
</script>

<template>
    <div class="w-full mt-8 border rounded-lg shadow-lg border-primary/40 bg-dark-card overflow-hidden">
        <div class="relative px-5 py-4 bg-gradient-to-r from-dark-sidebar to-dark-sidebar/70 border-b border-primary/30">
            <h2 class="text-xl font-bold text-white">
                Product Price Preview
            </h2>
            <p class="mt-1 text-sm text-gray-300">
                Select a product to view pricing across all user roles
            </p>
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10"></div>
        </div>

        <div class="p-4 sm:p-6">
            <!-- Product Selection -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="p-5 border rounded-xl bg-dark-sidebar/50 border-primary/30">
                    <label for="product_selector" class="block mb-2 text-sm font-medium text-gray-300">
                        Select Product
                    </label>
                    <select
                        id="product_selector"
                        v-model="selectedProductId"
                        class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-card focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="">Choose a product</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">
                            {{ product.nama }}
                        </option>
                    </select>

                    <div v-if="selectedProductId && previewData" class="flex flex-col items-center justify-center mt-8">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-primary/40 to-secondary/40 rounded-full blur-md -z-10 group-hover:blur-lg transition-all duration-300"></div>
                            <img 
                                :src="previewData.product.thumbnail" 
                                :alt="previewData.product.name" 
                                class="object-cover w-32 h-32 mx-auto rounded-full border-2 border-primary/40 shadow-lg shadow-primary/20 group-hover:scale-105 transition-all duration-300"
                                loading="lazy"
                            />
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">
                            {{ previewData.product.name }}
                        </h3>
                        <p class="text-sm text-gray-400">{{ previewData.product.brand }}</p>
                    </div>

                    <div v-else-if="loading" class="flex flex-col items-center justify-center py-10 mt-4">
                        <div class="w-12 h-12 border-4 rounded-full animate-spin border-primary border-t-transparent"></div>
                        <p class="mt-4 text-sm text-gray-300">Loading product details...</p>
                    </div>

                    <div v-else-if="!selectedProductId" class="py-10 mt-4 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-400">Select a product to view pricing</p>
                    </div>
                </div>

                <!-- Pricing Cards -->
                <div class="md:col-span-2">
                    <div v-if="error" class="p-4 text-sm text-white rounded-lg bg-red-500/30 border border-red-500/40">
                        {{ error }}
                    </div>

                    <div v-else-if="loading" class="flex items-center justify-center h-64">
                        <div class="w-12 h-12 border-4 rounded-full animate-spin border-primary border-t-transparent"></div>
                    </div>

                    <div v-else-if="!selectedProductId" class="flex flex-col items-center justify-center h-64 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-300">No Product Selected</h3>
                        <p class="mt-2 text-sm text-gray-400">Choose a product from the dropdown to view pricing</p>
                    </div>

                    <div v-else-if="previewData && previewData.pricing.length > 0" class="space-y-6 overflow-hidden">
                        <div v-for="service in previewData.pricing" :key="service.id" 
                            class="p-4 overflow-hidden transition-all duration-300 border rounded-lg shadow-lg animate-fade-in bg-dark-sidebar/30 border-primary/20 hover:border-primary/40">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-medium text-white">{{ service.name }}</h3>
                                    <p class="text-xs text-gray-400">{{ service.code }}</p>
                                </div>
                                <div class="px-3 py-1 text-sm rounded-lg bg-dark-card">
                                    <span class="text-gray-400">Base: </span>
                                    <span class="font-medium text-white">{{ formatPrice(service.base_price) }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2 md:grid-cols-3">
                                <template v-for="(rolePrice, index) in service.role_prices" :key="index">
                                    <div 
                                        :class="[
                                            'relative p-3 rounded-lg transition-all duration-300 hover:scale-105',
                                            getMinMaxPrices(service.role_prices).min?.roleId === rolePrice.role_id 
                                                ? 'bg-green-900/20 border border-green-500/30' 
                                                : getMinMaxPrices(service.role_prices).max?.roleId === rolePrice.role_id
                                                    ? 'bg-purple-900/20 border border-purple-500/30'
                                                    : 'bg-dark-card border border-gray-700/50'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-300">{{ rolePrice.role_name }}</span>
                                            
                                            <div v-if="rolePrice.profit_type" 
                                                :class="[
                                                    'px-2 py-0.5 text-xs rounded-full',
                                                    rolePrice.profit_type === 'percent' 
                                                        ? 'bg-blue-500/20 text-blue-400' 
                                                        : 'bg-purple-500/20 text-purple-400'
                                                ]"
                                            >
                                                {{ rolePrice.profit_type === 'percent' ? `+${rolePrice.profit_value}%` : `${rolePrice.profit_value}x` }}
                                            </div>
                                        </div>

                                        <div class="mt-2 text-lg font-bold text-white">
                                            {{ formatPrice(rolePrice.final_price) }}
                                        </div>

                                        <div v-if="rolePrice.profit_type" class="flex items-center mt-1">
                                            <div 
                                                :class="[
                                                    'flex items-center text-xs',
                                                    getProfitIndicator(service.base_price, rolePrice.final_price).type === 'positive'
                                                        ? 'text-green-400'
                                                        : 'text-red-400'
                                                ]"
                                            >
                                                <svg 
                                                    v-if="getProfitIndicator(service.base_price, rolePrice.final_price).type === 'positive'"
                                                    xmlns="http://www.w3.org/2000/svg" 
                                                    class="w-3 h-3 mr-1" 
                                                    fill="none" 
                                                    viewBox="0 0 24 24" 
                                                    stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                </svg>
                                                <svg 
                                                    v-else
                                                    xmlns="http://www.w3.org/2000/svg" 
                                                    class="w-3 h-3 mr-1" 
                                                    fill="none" 
                                                    viewBox="0 0 24 24" 
                                                    stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                                <span>
                                                    {{ getProfitIndicator(service.base_price, rolePrice.final_price).percentage }}%
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Min/Max Label -->
                                        <div v-if="getMinMaxPrices(service.role_prices).min?.roleId === rolePrice.role_id" 
                                            class="absolute px-2 py-0.5 text-xs text-white rounded-bl-lg bg-green-600/80 top-0 right-0">
                                            Lowest
                                        </div>
                                        <div v-else-if="getMinMaxPrices(service.role_prices).max?.roleId === rolePrice.role_id" 
                                            class="absolute px-2 py-0.5 text-xs text-white rounded-bl-lg bg-purple-600/80 top-0 right-0">
                                            Highest
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="previewData && previewData.pricing.length === 0" class="flex flex-col items-center justify-center h-64 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-300">No Services Found</h3>
                        <p class="mt-2 text-sm text-gray-400">This product has no active services</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

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
</style>
