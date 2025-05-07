<script setup>
import { ref, computed, onMounted, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import axios from "axios";
import CssCosmicParticles from "@/Components/User/Flashsale/CssCosmicParticles.vue";

const props = defineProps({
    show: Boolean,
    category: Object,
});

const emit = defineEmits(["close"]);

const searchQuery = ref("");
const isLoading = ref(true);
const isSubmitting = ref(false);
const page = ref(1);
const products = ref([]);
const pagination = ref({});
const selectedProducts = ref([]);
const errorMessage = ref("");
const successMessage = ref("");
const totalCount = ref(0);
const showSuccessAnimation = ref(false);

// Detect if device is low-powered
const isMobile = ref(window.innerWidth < 768);
const isLowPowerDevice = ref(
    navigator.hardwareConcurrency
        ? navigator.hardwareConcurrency < 4
        : isMobile.value
);

// Computed properties
const selectedCount = computed(() => selectedProducts.value.length);
const hasSelectedProducts = computed(() => selectedCount.value > 0);

// Load products
const loadProducts = async () => {
    isLoading.value = true;
    errorMessage.value = "";

    try {
        const response = await axios.get(
            route("categories.available-products", props.category.id),
            {
                params: {
                    search: searchQuery.value,
                    page: page.value,
                },
            }
        );

        products.value = response.data.products.data;
        pagination.value = {
            total: response.data.products.total,
            perPage: response.data.products.per_page,
            currentPage: response.data.products.current_page,
            lastPage: response.data.products.last_page,
            links: response.data.products.links,
        };

        // Update total count
        totalCount.value = response.data.products.total;
    } catch (error) {
        console.error("Error loading products:", error);
        errorMessage.value = "Failed to load products. Please try again.";
    } finally {
        isLoading.value = false;
    }
};

// Handle pagination
const goToPage = (newPage) => {
    page.value = newPage;
};

// Toggle product selection
const toggleSelection = (productId) => {
    if (selectedProducts.value.includes(productId)) {
        selectedProducts.value = selectedProducts.value.filter(
            (id) => id !== productId
        );
    } else {
        selectedProducts.value.push(productId);
    }
};

// Select all products on current page
const selectAllOnPage = () => {
    const currentIds = products.value.map((product) => product.id);

    // Check if all current page products are already selected
    const allSelected = currentIds.every((id) =>
        selectedProducts.value.includes(id)
    );

    if (allSelected) {
        // Unselect all on current page
        selectedProducts.value = selectedProducts.value.filter(
            (id) => !currentIds.includes(id)
        );
    } else {
        // Select all on current page
        const newSelected = [
            ...new Set([...selectedProducts.value, ...currentIds]),
        ];
        selectedProducts.value = newSelected;
    }
};

// Check if all products on current page are selected
const isAllOnPageSelected = computed(() => {
    if (products.value.length === 0) return false;
    return products.value.every((product) =>
        selectedProducts.value.includes(product.id)
    );
});

// Submit bulk assignment
const submitBulkAssign = async () => {
    if (selectedProducts.value.length === 0) {
        errorMessage.value = "Please select at least one product.";
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        const response = await axios.post(
            route("categories.bulk-assign", props.category.id),
            {
                product_ids: selectedProducts.value,
            }
        );

        successMessage.value =
            response.data.message ||
            `${selectedProducts.value.length} products have been assigned.`;

        // Show success animation
        showSuccessAnimation.value = true;

        // Reset selection
        selectedProducts.value = [];

        // Reload products after delay
        setTimeout(() => {
            loadProducts();
            showSuccessAnimation.value = false;
        }, 2000);
    } catch (error) {
        console.error("Error assigning products:", error);
        errorMessage.value =
            error.response?.data?.message || "Failed to assign products.";
    } finally {
        isSubmitting.value = false;
    }
};

// Close modal
const close = () => {
    page.value = 1;
    searchQuery.value = "";
    selectedProducts.value = [];
    errorMessage.value = "";
    successMessage.value = "";
    showSuccessAnimation.value = false;
    emit("close", true); // Always refresh after closing
};

// Handle search debounce
watch(
    searchQuery,
    () => {
        page.value = 1; // Reset to first page on search
        loadProducts();
    },
    { debounce: 300 }
);

// Load products on page change
watch(page, () => {
    loadProducts();
});

// Load products on show
watch(
    () => props.show,
    (val) => {
        if (val) {
            loadProducts();
        }
    }
);

// Initialize
onMounted(() => {
    if (props.show) {
        loadProducts();
    }
});
</script>

<template>
    <Modal :show="show" @close="close" max-width="2xl">
        <div
            class="relative border border-gray-700 rounded-lg p-3 sm:p-4 md:p-6 bg-dark-card max-h-[85vh] overflow-hidden"
        >
            <!-- Cosmic Background -->
            <div class="absolute inset-0 z-0 overflow-hidden">
                <CssCosmicParticles />

                <!-- Wormhole Effect -->
                <div v-if="showSuccessAnimation" class="wormhole-effect"></div>
            </div>

            <!-- Modal Content -->
            <div class="relative z-10">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="flex items-center space-x-2 text-xl font-bold text-white"
                    >
                        <span
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/20 text-primary"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                />
                            </svg>
                        </span>
                        <span>Manage Products</span>

                        <!-- Selected Count Badge -->
                        <span
                            v-if="hasSelectedProducts"
                            class="ml-2 px-2.5 py-0.5 text-xs bg-secondary/20 text-secondary border border-secondary/30 rounded-full flex items-center"
                        >
                            {{ selectedCount }} selected
                            <span
                                v-if="!isLowPowerDevice"
                                class="comet-trail"
                            ></span>
                        </span>
                    </h3>
                    <button
                        @click="close"
                        class="text-gray-400 hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Current Category Info -->
                <div
                    class="p-3 mb-4 border rounded-lg bg-dark-lighter border-primary/30 animate-fade-in"
                >
                    <p class="text-sm text-gray-400">Target Category</p>
                    <div class="flex items-center space-x-2">
                        <h4 class="font-medium text-white">
                            {{ category?.kategori_name }}
                        </h4>
                        <span
                            v-if="category?.kode_kategori"
                            class="px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs rounded-full border border-orange-500/30 nebula-pulse"
                        >
                            {{ category.kode_kategori }}
                        </span>
                    </div>
                </div>

                <!-- Error & Success Messages -->
                <div
                    v-if="errorMessage"
                    class="p-2 mb-4 text-sm text-red-400 border rounded-md bg-red-500/20 border-red-500/30 animate-fade-in"
                >
                    {{ errorMessage }}
                </div>

                <div
                    v-if="successMessage"
                    class="p-2 mb-4 text-sm text-green-400 border rounded-md bg-green-500/20 border-green-500/30 animate-fade-in"
                >
                    {{ successMessage }}
                </div>

                <!-- Search & Controls -->
                <div
                    class="flex flex-col mb-4 space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2"
                >
                    <!-- Search Input -->
                    <div class="relative flex-grow">
                        <div
                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="w-full py-2 pl-10 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Search products..."
                            :disabled="isLoading"
                        />
                    </div>

                    <!-- Select All Checkbox -->
                    <button
                        @click="selectAllOnPage"
                        class="flex items-center justify-center px-4 py-2 space-x-2 transition-all border rounded-lg"
                        :class="[
                            isAllOnPageSelected
                                ? 'bg-secondary/20 border-secondary/50 text-secondary'
                                : 'bg-dark-sidebar border-gray-700 text-gray-300 hover:border-primary/30',
                        ]"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                        <span>{{
                            isAllOnPageSelected ? "Deselect All" : "Select All"
                        }}</span>
                    </button>
                </div>

                <!-- Products Grid -->
                <div class="overflow-y-auto max-h-[50vh] cosmic-scrollbar pr-1">
                    <!-- Loading state -->
                    <div v-if="isLoading" class="flex justify-center py-12">
                        <div
                            class="w-12 h-12 border-4 rounded-full border-primary border-t-transparent animate-spin"
                        ></div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else-if="products.length === 0"
                        class="py-12 text-center"
                    >
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-dark-sidebar"
                        >
                            <span class="text-2xl">📦</span>
                        </div>
                        <h4 class="text-lg font-medium text-white">
                            No products found
                        </h4>
                        <p class="mt-1 text-gray-400">
                            Try adjusting your search criteria
                        </p>
                    </div>

                    <!-- Products grid -->
                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="relative p-3 overflow-hidden transition-all border rounded-lg cursor-pointer product-card"
                            :class="[
                                selectedProducts.includes(product.id)
                                    ? product.kategori_id === category.id
                                        ? 'bg-blue-500/20 border-blue-500/40 text-blue-400'
                                        : 'bg-primary/20 border-primary/40 text-white'
                                    : product.kategori_id === category.id
                                    ? 'bg-blue-500/10 border-blue-500/30'
                                    : 'bg-dark-lighter border-gray-700 hover:border-primary/30',
                            ]"
                            @click="toggleSelection(product.id)"
                        >
                            <!-- Cosmic glow effect -->
                            <div
                                v-if="
                                    selectedProducts.includes(product.id) &&
                                    !isLowPowerDevice
                                "
                                class="absolute inset-0 z-0 opacity-30"
                            >
                                <div class="cosmic-glow"></div>
                            </div>

                            <div class="relative z-10 flex">
                                <!-- Product Image -->
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-16 h-16 mr-3 overflow-hidden border rounded-md bg-dark-sidebar border-gray-700/50"
                                >
                                    <img
                                        v-if="product.thumbnail"
                                        :src="'/storage/' + product.thumbnail"
                                        :alt="product.nama"
                                        class="object-cover w-full h-full"
                                        loading="lazy"
                                    />
                                    <span v-else class="text-2xl">🎮</span>
                                </div>

                                <!-- Product Info -->
                                <div class="flex-grow">
                                    <h4
                                        class="font-medium text-white line-clamp-1"
                                    >
                                        {{ product.nama }}
                                    </h4>

                                    <!-- Current Category -->
                                    <div class="flex items-center mt-2">
                                        <span class="mr-1 text-xs text-gray-400"
                                            >Category:</span
                                        >
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full"
                                            :class="[
                                                product.kategori_id ===
                                                category.id
                                                    ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
                                                    : 'bg-dark-sidebar text-gray-300 border border-gray-700',
                                            ]"
                                        >
                                            {{
                                                product.kategori
                                                    ?.kategori_name || "None"
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Selection Indicator -->
                                <div
                                    v-if="selectedProducts.includes(product.id)"
                                    class="absolute w-5 h-5 top-2 right-2 text-primary"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <!-- Quantum entanglement lines (when selected) -->
                            <div
                                v-if="
                                    selectedProducts.includes(product.id) &&
                                    !isLowPowerDevice
                                "
                                class="quantum-lines"
                            ></div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="pagination.lastPage > 1"
                        class="flex justify-center mt-4 space-x-2"
                    >
                        <button
                            v-for="pageNum in pagination.lastPage"
                            :key="pageNum"
                            @click="goToPage(pageNum)"
                            class="flex items-center justify-center w-8 h-8 transition-all rounded-full"
                            :class="[
                                pageNum === pagination.currentPage
                                    ? 'bg-primary text-white'
                                    : 'bg-dark-sidebar text-gray-400 hover:bg-dark-lighter',
                            ]"
                        >
                            {{ pageNum }}
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex flex-col justify-end mt-6 space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2"
                >
                    <button
                        @click="close"
                        class="px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitBulkAssign"
                        :disabled="isSubmitting || !hasSelectedProducts"
                        class="flex items-center justify-center px-4 py-2 space-x-1 text-white transition-all rounded-lg"
                        :class="[
                            isSubmitting || !hasSelectedProducts
                                ? 'bg-primary/50 cursor-not-allowed'
                                : 'bg-primary hover:bg-primary-hover hover:shadow-glow-primary',
                        ]"
                    >
                        <svg
                            v-if="isSubmitting"
                            class="w-5 h-5 text-white animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <span v-else class="text-lg">🪄</span>
                        <span>{{
                            isSubmitting
                                ? "Assigning Products..."
                                : "Assign Selected Products"
                        }}</span>
                    </button>
                </div>
            </div>

            <!-- Success Animation Layer -->
            <div
                v-if="showSuccessAnimation"
                class="absolute inset-0 z-20 flex items-center justify-center"
            >
                <div class="supernova-explosion">
                    <div class="count-display">{{ selectedCount }}</div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Styles for the cosmic scrollbar */
.cosmic-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(155, 135, 245, 0.3) rgba(31, 41, 55, 0.5);
}

.cosmic-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.cosmic-scrollbar::-webkit-scrollbar-track {
    background: rgba(31, 41, 55, 0.5);
    border-radius: 8px;
}

.cosmic-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(155, 135, 245, 0.3);
    border-radius: 8px;
    border: 1px solid rgba(155, 135, 245, 0.1);
}

/* Animations */
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Cosmic glow effect */
.cosmic-glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(
        circle at center,
        rgba(155, 135, 245, 0.2) 0%,
        transparent 70%
    );
    animation: pulse-glow 2s infinite alternate;
}

@keyframes pulse-glow {
    0% {
        opacity: 0.5;
        transform: scale(0.8);
    }
    100% {
        opacity: 0.8;
        transform: scale(1.1);
    }
}

/* Quantum entanglement lines */
.quantum-lines {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.quantum-lines::before,
.quantum-lines::after {
    content: "";
    position: absolute;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(155, 135, 245, 0.5),
        transparent
    );
    width: 100%;
    height: 1px;
    opacity: 0.6;
    animation: quantum-line 3s infinite linear;
}

.quantum-lines::before {
    top: 30%;
}

.quantum-lines::after {
    top: 70%;
    animation-delay: 1.5s;
}

@keyframes quantum-line {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Comet trail for selected count */
.comet-trail {
    position: absolute;
    width: 15px;
    height: 2px;
    right: -15px;
    top: 50%;
    background: linear-gradient(to right, rgba(51, 195, 240, 0.8), transparent);
    transform: translateY(-50%);
    animation: comet-pulse 1.5s infinite;
}

@keyframes comet-pulse {
    0%,
    100% {
        opacity: 0.3;
        width: 10px;
    }
    50% {
        opacity: 0.8;
        width: 15px;
    }
}

/* Pulsing nebula borders */
.nebula-pulse {
    animation: nebula-border-pulse 3s infinite alternate;
}

@keyframes nebula-border-pulse {
    0% {
        box-shadow: 0 0 5px rgba(249, 115, 22, 0.3);
    }
    100% {
        box-shadow: 0 0 15px rgba(249, 115, 22, 0.7);
    }
}

/* Supernova explosion for success */
.supernova-explosion {
    position: relative;
    width: 150px;
    height: 150px;
    background: radial-gradient(
        circle,
        rgba(249, 115, 22, 0.8) 0%,
        rgba(249, 115, 22, 0.1) 70%,
        transparent 100%
    );
    border-radius: 50%;
    animation: supernova 1.5s ease-out;
    display: flex;
    align-items: center;
    justify-content: center;
    will-change: transform, opacity;
    transform: translateZ(0);
}

.count-display {
    font-size: 3rem;
    font-weight: bold;
    color: white;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
    animation: count-zoom 1s ease-out;
}

@keyframes supernova {
    0% {
        transform: scale(0.1);
        opacity: 0;
    }
    40% {
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

@keyframes count-zoom {
    0% {
        transform: scale(0.5);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Wormhole effect */
.wormhole-effect {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300px;
    height: 300px;
    margin-left: -150px;
    margin-top: -150px;
    background: conic-gradient(
        from 0deg,
        rgba(155, 135, 245, 0),
        rgba(155, 135, 245, 0.2),
        rgba(51, 195, 240, 0.3),
        rgba(155, 135, 245, 0.2),
        rgba(155, 135, 245, 0)
    );
    border-radius: 50%;
    animation: wormhole-spin 2s linear infinite;
    opacity: 0.5;
    z-index: 0;
    will-change: transform;
}

@keyframes wormhole-spin {
    0% {
        transform: translate(-50%, -50%) rotate(0deg) scale(0.5);
    }
    100% {
        transform: translate(-50%, -50%) rotate(360deg) scale(1.5);
    }
}

/* Optimize performant animations */
.product-card {
    will-change: transform, box-shadow;
    transform: translateZ(0);
}

/* Prevent animations for users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
    .quantum-lines,
    .cosmic-glow,
    .comet-trail,
    .nebula-pulse,
    .wormhole-effect {
        animation: none;
    }

    .supernova-explosion {
        background: radial-gradient(
            circle,
            rgba(249, 115, 22, 0.8) 0%,
            rgba(249, 115, 22, 0.1) 70%,
            transparent 100%
        );
        animation: none;
    }

    .count-display {
        animation: none;
    }
}
</style>
