<script setup>
import { computed, ref, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import CssCosmicParticles from "./CssCosmicParticles.vue";

const props = defineProps({
    flashItem: {
        type: Object,
        required: true,
    },
});

const layanan = computed(() => props.flashItem.layanan);
const produk = computed(() => layanan.value.produk);
const cardRef = ref(null);

// Detect device capabilities
const isMobile = computed(() => window?.innerWidth < 768);
const isLowPowerDevice = computed(() => {
    return navigator.hardwareConcurrency
        ? navigator.hardwareConcurrency < 4
        : isMobile.value;
});

// Calculate discount percentage
const discountPercentage = computed(() => {
    const original = layanan.value.harga_jual;
    const sale = props.flashItem.harga_flashsale;

    if (!original || !sale || original <= 0) return 0;

    const percentage = Math.round(((original - sale) / original) * 100);
    return percentage;
});

// Format price with IDR
const formatPrice = (price) => {
    if (!price) return "Rp 0";

    // Bulatkan ke angka terdekat
    const roundedPrice = Math.round(price);

    // Format dengan titik sebagai pemisah ribuan
    const formatted = roundedPrice
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    return "Rp " + formatted;
};

// Calculate stock percentage
const stockPercentage = computed(() => {
    // If no stock tracking, return 100%
    if (props.flashItem.stok_tersedia === null) return 100;

    const total =
        props.flashItem.stok_tersedia + (props.flashItem.stok_terjual || 0);

    // Prevent division by zero
    if (total <= 0) return 0;

    const percentage = (props.flashItem.stok_tersedia / total) * 100;

    return Math.max(0, Math.min(100, percentage)); // Clamp between 0-100
});

// Get thumbnail image
const thumbnailImage = computed(() => {
    if (layanan.value && layanan.value.thumbnail) {
        return layanan.value.thumbnail;
    }
    return null;
});

// Check if stock is low (less than 20%)
const isStockLow = computed(() => {
    return stockPercentage.value < 20;
});

// Set particle density based on device
const particleDensity = computed(() => {
    if (isLowPowerDevice.value) return 0.3; // Even lower for better performance
    return 0.5; // Reduced from 1.0 to 0.5 for better performance
});

// Create URL params for order page
const orderPageParams = computed(() => {
    if (!layanan.value || !produk.value) return {};

    return {
        productId: produk.value.id,
        flashsaleId: props.flashItem.id,
        layananId: layanan.value.id,
    };
});

onMounted(() => {
    // Apply simpler parallax effect on desktop (optimized)
    if (window.innerWidth >= 768 && !isLowPowerDevice.value) {
        const handleMouseMove = (e) => {
            if (!cardRef.value) return;

            const rect = cardRef.value.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            // Reduced movement and optimized for performance
            const moveX = (x - centerX) / 40;
            const moveY = (y - centerY) / 40;

            const cosmicLayer = cardRef.value.querySelector(".cosmic-layer");
            if (cosmicLayer) {
                cosmicLayer.style.transform = `translate(${moveX}px, ${moveY}px)`;
            }
        };

        // Use passive event listener for better performance
        cardRef.value.addEventListener("mousemove", handleMouseMove, {
            passive: true,
        });
        cardRef.value.addEventListener("mouseleave", () => {
            const cosmicLayer = cardRef.value.querySelector(".cosmic-layer");
            if (cosmicLayer) {
                cosmicLayer.style.transform = "translate(0, 0)";
            }
        });
    }
});
</script>

<template>
    <!-- Link to order page with flashsale item pre-selected -->
    <Link
        :href="`/order/${produk.slug}`"
        :data="orderPageParams"
        class="block cursor-pointer"
    >
        <div
            ref="cardRef"
            class="transition-transform border flashsale-card group bg-primary/20 border-secondary/20 hover:-translate-y-1"
        >
            <!-- User Limit Badge -->
            <div
                v-if="flashItem.batas_user"
                class="absolute z-20 top-2 right-2"
            >
                <div
                    class="px-2 py-1 text-xs text-white border rounded-full bg-primary/10 border-primary/50"
                >
                    <span class="flex items-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-3 h-3 mr-1"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                            />
                        </svg>
                        Max. {{ flashItem.batas_user }}/user
                    </span>
                </div>
            </div>

            <!-- Card Body - Updated Layout -->
            <div class="card-content">
                <!-- Left Section -->
                <div class="left-section">
                    <!-- Product Info -->
                    <div class="product-info">
                        <h3 class="product-name">
                            {{ layanan.nama_layanan }}
                        </h3>
                        <p class="product-category">{{ produk.nama }}</p>
                    </div>

                    <!-- Price Section -->
                    <div class="flex items-center mt-1 space-x-2">
                        <!-- Thumbnail image -->
                        <div
                            v-if="thumbnailImage"
                            class="flex-shrink-0 w-10 h-10 overflow-hidden rounded"
                        >
                            <img
                                :src="'/storage/' + thumbnailImage"
                                alt="Product"
                                class="object-cover w-full h-full"
                            />
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-gray-800 rounded"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-gray-500"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                    ry="2"
                                ></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <div>
                            <div class="flash-price">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="flash-icon"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                                </svg>
                                {{ formatPrice(flashItem.harga_flashsale) }}
                            </div>
                            <div class="regular-price">
                                <span>{{
                                    formatPrice(layanan.harga_jual)
                                }}</span>
                                <span
                                    v-if="discountPercentage > 0"
                                    class="discount-badge"
                                >
                                    -{{ discountPercentage }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Section - Simplified Cosmic Elements -->
                <div class="right-section">
                    <div class="cosmic-layer">
                        <!-- Simplified CSS Planet -->
                        <div class="cosmic-planet"></div>
                        <div class="planet-ring"></div>

                        <!-- Use minimal CSS particles -->
                        <CssCosmicParticles
                            v-if="!isLowPowerDevice"
                            :density="particleDensity"
                            theme="primary"
                        />
                    </div>

                    <div class="thermal-edge"></div>
                </div>
            </div>

            <!-- Enhanced Card Footer - Progress Bar -->
            <div class="card-footer bg-content_background/20">
                <!-- Progress Container with Integrated Text -->
                <div class="relative progress-container-wrapper">
                    <!-- Stock text moved above progress bar -->
                    <div
                        class="text-xs absolute inset-x-0 top-[-18px] text-white opacity-90"
                    >
                        <span v-if="flashItem.stok_tersedia !== null">
                            Tersisa {{ flashItem.stok_tersedia }}
                        </span>
                        <span v-else>Stok tersedia</span>
                    </div>

                    <!-- Progress Bar with enhanced styling -->
                    <div class="progress-container px-1 py-0.5 rounded-full">
                        <div
                            class="h-3 rounded-full progress-bar"
                            :class="[isStockLow ? 'low-stock' : '']"
                            :style="{ width: `${stockPercentage}%` }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.flashsale-card {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    aspect-ratio: 5/3;
    height: 200px;
    max-width: 100%;
    transform-origin: center bottom;
    will-change: transform;
}

/* Card content layout */
.card-content {
    display: flex;
    height: 80%;
    width: 100%;
    position: relative;
    overflow: hidden;
}

/* Left section - Product info and pricing */
.left-section {
    width: 70%;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    z-index: 10;
}

/* Product image styles */
.product-image {
    width: 80px;
    height: 80px;
    position: relative;
    margin-bottom: 0.75rem;
    overflow: hidden;
    background: transparent;
    border-radius: 0.5rem;
}

.product-name {
    font-size: 1rem;
    font-weight: bold;
    color: #33c3f0; /* secondary color */
    margin-bottom: 0.25rem;
}

.product-category {
    font-size: 0.75rem;
    color: #cbd5e1;
    margin-bottom: 0.5rem;
}

/* Price section */
/* .price-section {
    margin-top: auto;
} */

.flash-price {
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: bold;
    color: #33c3f0;
}

.flash-icon {
    width: 16px;
    height: 16px;
    margin-right: 0.25rem;
    color: #33c3f0; /* secondary color */
}

.regular-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.regular-price span:first-child {
    text-decoration: line-through;
    font-size: 0.75rem;
    color: #94a3b8;
    opacity: 0.7;
}

.discount-badge {
    background-color: #9b87f5;
    color: white;
    font-size: 0.65rem;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}

/* Right section - CSS-based Cosmic elements */
.right-section {
    width: 30%;
    position: relative;
    overflow: hidden;
}

.cosmic-layer {
    position: absolute;
    inset: 0;
    transition: transform 0.3s ease;
}

/* CSS-based planet - simplified */
.cosmic-planet {
    position: absolute;
    width: 40px;
    height: 40px;
    right: 30px;
    top: 60px;
    border-radius: 50%;
    background: radial-gradient(
        circle at 30% 30%,
        rgba(255, 255, 255, 0.4),
        rgba(51, 195, 240, 0.2) 50%,
        transparent
    );
    box-shadow: inset -2px -2px 4px rgba(0, 0, 0, 0.5);
}

.planet-ring {
    position: absolute;
    width: 70px;
    height: 15px;
    right: 15px;
    top: 70px;
    border-radius: 50%;
    border: 1px solid rgba(155, 135, 245, 0.3);
    transform: rotate(-30deg);
}

.planet-ring::after {
    content: "";
    position: absolute;
    top: -50%;
    left: 10%;
    width: 80%;
    height: 200%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(51, 195, 240, 0.2),
        transparent
    );
    transform: rotate(10deg);
}

/* Thermal edge effect */
.thermal-edge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 70%;
    height: 70%;
    background: radial-gradient(
        circle at bottom right,
        rgba(255, 100, 50, 0.15),
        transparent 70%
    );
    mask-image: linear-gradient(135deg, transparent, rgba(0, 0, 0, 0.7));
    z-index: 1;
}

/* Card footer */
.card-footer {
    height: 20%;
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

/* Progress container wrapper with positioning context */
.progress-container-wrapper {
    margin-top: 18px; /* Make space for the text above */
}

/* Enhanced progress container */
.progress-container {
    height: calc(6px + 0.5rem); /* Base height + padding */
    background-color: rgba(255, 255, 255, 0.1);
    overflow: hidden;
    position: relative;
    backdrop-filter: blur(4px);
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Enhanced progress bar */
.progress-bar {
    height: 100%;
    background-color: #9b87f5; /* primary */
    transition: width 1s ease-out;
    position: relative;
    box-shadow: 0 0 10px rgba(155, 135, 245, 0.5);
    background-image: linear-gradient(
        90deg,
        rgba(155, 135, 245, 0.8),
        rgba(155, 135, 245, 1) 50%,
        rgba(155, 135, 245, 0.8)
    );
}

.progress-bar.low-stock {
    background-color: #ef4444; /* Red for low stock */
    background-image: linear-gradient(
        90deg,
        rgba(239, 68, 68, 0.8),
        rgba(239, 68, 68, 1) 50%,
        rgba(239, 68, 68, 0.8)
    );
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
}

/* Hexagonal grid pattern overlay - lightweight version */
.flashsale-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(
        rgba(155, 135, 245, 0.1) 2px,
        transparent 2px
    );
    background-size: 16px 16px;
    background-position: -8px -8px;
    opacity: 0.2;
    z-index: 0;
    pointer-events: none;
}

/* Media queries for responsive design */
@media (max-width: 768px) {
    .left-section {
        width: 70%;
    }

    .right-section {
        width: 30%;
    }

    .product-image {
        width: 60px;
        height: 60px;
    }

    .cosmic-planet {
        width: 40px;
        height: 40px;
        right: 30px;
        top: 60px;
    }

    .planet-ring {
        width: 60px;
        height: 16px;
        right: 20px;
        top: 72px;
    }
}

/* Prevent animations for users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
    .cosmic-planet {
        animation: none;
    }
}
</style>
