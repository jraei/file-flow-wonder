<script setup>
import { computed, ref, onMounted } from "vue";
import CosmicParticles from "./CosmicParticles.vue";

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
    if (
        layanan.value &&
        layanan.value.gambar &&
        layanan.value.gambar.length > 0
    ) {
        return layanan.value.gambar;
    }
    return null;
});

// Check if stock is low (less than 20%)
const isStockLow = computed(() => {
    return stockPercentage.value < 20;
});

// Instead of generating cosmic elements as DOM nodes, use a unique ID for canvas
const cardId = ref(
    `flashcard-${Date.now()}-${Math.floor(Math.random() * 1000)}`
);

// Set particle density based on device
const particleDensity = computed(() => {
    if (isLowPowerDevice.value) return 0.5;
    return 1;
});

onMounted(() => {
    // Apply parallax effect on desktop (simplified and optimized)
    if (window.innerWidth >= 768 && !isLowPowerDevice.value) {
        const handleMouseMove = (e) => {
            if (!cardRef.value) return;

            const rect = cardRef.value.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const moveX = (x - centerX) / 20;
            const moveY = (y - centerY) / 20;

            const cosmicLayer = cardRef.value.querySelector(".cosmic-layer");
            if (cosmicLayer) {
                // Use transform with will-change for better performance
                cosmicLayer.style.transform = `translate(${moveX}px, ${moveY}px)`;
            }
        };

        cardRef.value.addEventListener("mousemove", handleMouseMove);
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
    <div ref="cardRef" class="flashsale-card group" :id="cardId">
        <!-- User Limit Badge -->
        <div v-if="flashItem.batas_user" class="absolute z-20 top-2 right-2">
            <div
                class="px-2 py-1 text-xs text-white border rounded-full bg-primary border-primary"
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
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    Max. {{ flashItem.batas_user }}/user
                </span>
            </div>
        </div>

        <!-- Card Body - New Layout -->
        <div class="card-content">
            <!-- Left Section -->
            <div class="left-section">
                <!-- Product Image -->
                <div class="product-image">
                    <img
                        v-if="thumbnailImage"
                        :src="'/storage/' + thumbnailImage"
                        alt=""
                        loading="lazy"
                    />
                    <div class="image-glow"></div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h3 class="product-name">
                        {{ layanan.nama_layanan }}
                    </h3>
                    <p class="product-category">{{ produk.nama }}</p>
                </div>

                <!-- Price Section -->
                <div class="price-section">
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
                        <span>{{ formatPrice(layanan.harga_jual) }}</span>
                        <span
                            v-if="discountPercentage > 0"
                            class="discount-badge"
                        >
                            -{{ discountPercentage }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Section - Cosmic Elements (Optimized) -->
            <div class="right-section">
                <div class="cosmic-layer" style="will-change: transform">
                    <!-- Replace static elements with canvas-based particles -->
                    <CosmicParticles
                        :item-id="cardId"
                        :density="particleDensity"
                        theme="primary"
                    />
                </div>

                <!-- Thermal Edge Effect (static gradient) -->
                <div class="thermal-edge"></div>
            </div>
        </div>

        <!-- Enhanced Card Footer - Progress Bar -->
        <div class="card-footer">
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
                    >
                        <!-- Simplified particle sparks (low count for performance) -->
                        <div class="particle-sparks" v-if="!isLowPowerDevice">
                            <div class="spark" v-for="i in 3" :key="i"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.flashsale-card {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(155, 135, 245, 0.5);
    border-radius: 1rem;
    background: rgba(33, 92, 187, 1); /* Deep space blue */
    aspect-ratio: 5/3;
    height: 240px;
    max-width: 100%;
    transform-origin: center bottom;
}

.flashsale-card:hover {
    transform: translateY(-5px) scale(1.02);
    /* box-shadow: 0 0 25px rgba(155, 135, 245, 0.3); */
    border: 2px solid rgba(155, 135, 245, 1);
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
    width: 50%;
    padding: 0.75rem;
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
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.flashsale-card:hover .product-image img {
    transform: scale(1.1);
}

.image-glow {
    position: absolute;
    inset: 0;
    /* border-radius: 50%; */
    box-shadow: 0 0 10px rgba(155, 135, 245, 0.7);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.flashsale-card:hover .image-glow {
    opacity: 1;
}

.product-name {
    font-size: 1rem;
    font-weight: bold;
    color: #33c3f0; /* secondary color */
    margin-bottom: 0.25rem;
    transition: all 0.3s ease;
}

.flashsale-card:hover .product-name {
    text-shadow: 0 0 8px rgba(155, 135, 245, 0.7);
    transform: scale(1.02);
}

.product-category {
    font-size: 0.75rem;
    color: #cbd5e1;
    margin-bottom: 0.5rem;
}

/* Price section */
.price-section {
    margin-top: auto;
}

.flash-price {
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: bold;
    color: #33c3f0;
    animation: price-pulse 3s infinite alternate;
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

/* Right section - Cosmic elements */
.right-section {
    width: 50%;
    position: relative;
    overflow: hidden;
}

.cosmic-layer {
    position: absolute;
    inset: 0;
    transition: transform 0.3s ease;
}

/* Cosmic elements */
.cosmic-planet {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(
        circle at 30% 30%,
        rgba(255, 255, 255, 0.4),
        rgba(51, 195, 240, 0.2) 50%,
        transparent
    );
    box-shadow: inset -2px -2px 4px rgba(0, 0, 0, 0.5);
    animation: orbit-rotation linear infinite;
}

.planet-ring {
    position: absolute;
    width: 150%;
    height: 30%;
    top: 35%;
    left: -25%;
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

.cosmic-pulsar {
    position: absolute;
    border-radius: 50%;
    background-color: #33c3f0;
    box-shadow: 0 0 10px #33c3f0, 0 0 20px rgba(51, 195, 240, 0.5);
    animation: pulsar-glow ease-in-out infinite;
}

.quantum-wave {
    position: absolute;
    width: 40px;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(155, 135, 245, 0.1),
        rgba(155, 135, 245, 0.3),
        rgba(155, 135, 245, 0.1),
        transparent
    );
    animation: quantum-wave ease-in-out infinite;
    transform-origin: center center;
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
    border-top: 1px solid rgba(155, 135, 245, 0.1);
    background-color: rgba(
        33,
        92,
        187,
        0.9
    ); /* Darker version of card background */
    display: flex;
    flex-direction: column;
    justify-content: flex-end; /* Align to bottom for text above */
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

/* Enhanced particle sparks for thermal effect */
.particle-sparks {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 25px;
    overflow: hidden;
}

.spark {
    position: absolute;
    width: 2px;
    height: 2px;
    background-color: rgba(255, 200, 50, 0.8);
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(255, 200, 50, 0.6);
    animation: spark-float 2s infinite ease-out;
    transform: translateZ(0); /* Hardware acceleration */
}

/* Hexagonal grid pattern overlay */
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

/* CRT scanline effect */
.flashsale-card::after {
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
    z-index: 5;
    opacity: 0.05;
}

/* Animations */
@keyframes price-pulse {
    0% {
        text-shadow: 0 0 5px rgba(155, 135, 245, 0.3);
    }
    100% {
        text-shadow: 0 0 12px rgba(155, 135, 245, 0.7);
    }
}

@keyframes orbit-rotation {
    0% {
        transform: rotate(0deg) translateX(5px) rotate(0deg);
    }
    100% {
        transform: rotate(360deg) translateX(5px) rotate(-360deg);
    }
}

@keyframes pulsar-glow {
    0%,
    100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.2);
        opacity: 1;
        box-shadow: 0 0 15px #33c3f0, 0 0 30px rgba(51, 195, 240, 0.5);
    }
}

@keyframes quantum-wave {
    0% {
        opacity: 0.3;
        transform: scaleY(0.7) rotate(10deg);
    }
    50% {
        opacity: 0.6;
        transform: scaleY(1) rotate(-5deg);
    }
    100% {
        opacity: 0.3;
        transform: scaleY(0.7) rotate(10deg);
    }
}

@keyframes spark-float {
    0% {
        transform: translateY(0) scale(0.8);
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        transform: translateY(-10px) scale(1.2);
        opacity: 0;
    }
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

    /* Hide some cosmic elements on mobile */
    .cosmic-planet:nth-child(3),
    .cosmic-planet:nth-child(4),
    .cosmic-pulsar:nth-child(3) {
        display: none;
    }

    /* Increase scroll speed by 30% (handled in parent component) */
}

/* Additional hardware acceleration for smooth animations */
.flashsale-card {
    transform: translateZ(0);
    backface-visibility: hidden;
}

.cosmic-planet,
.cosmic-pulsar,
.quantum-wave {
    transform: translateZ(0);
    will-change: transform, opacity;
}

@media (prefers-reduced-motion: reduce) {
    /* Reduce motion for accessibility */
    .progress-bar {
        transition: width 2s linear;
    }

    .spark {
        animation: none;
    }

    .price-section {
        animation: none;
    }
}

/* Optimized animations with low impact */
@keyframes price-pulse {
    0% {
        text-shadow: 0 0 5px rgba(155, 135, 245, 0.3);
    }
    100% {
        text-shadow: 0 0 12px rgba(155, 135, 245, 0.7);
    }
}

@keyframes spark-float {
    0% {
        transform: translateY(0) scale(0.8);
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        transform: translateY(-10px) scale(1.2);
        opacity: 0;
    }
}

/* Hardware acceleration hints */
.flashsale-card {
    will-change: transform;
    transform: translateZ(0);
    backface-visibility: hidden;
}

.cosmic-layer {
    will-change: transform;
}

.progress-bar {
    will-change: width;
}

/* Additional hardware acceleration for smooth animations */
.progress-container {
    transform: translateZ(0);
}

/* Media optimizations */
@media (max-width: 768px) {
    .spark:nth-child(even) {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    /* Reduce motion for accessibility */
    .progress-bar {
        transition: width 2s linear;
    }

    .spark {
        animation: none;
    }

    .price-section {
        animation: none;
    }
}
</style>
