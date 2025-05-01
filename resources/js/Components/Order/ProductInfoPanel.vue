
<script setup>
import { ref, onMounted, onUnmounted, reactive } from "vue";
import { Rocket, MessageSquare, Shield } from "lucide-vue-next";

const props = defineProps({
    produk: Object,
    minPrice: Number,
});

// Device and performance detection
const isMobile = ref(false);
const isReducedMotion = ref(false);

// Handle responsive behavior
const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
};

// Monitor system preferences
const checkPreferences = () => {
    isReducedMotion.value = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;
};

// Animation performance control
let meteorsActive = ref(true);
const toggleMeteors = (active) => {
    meteorsActive.value = active;
};

// Vue-controlled meteor system
const meteors = reactive([]);
const meteorContainer = ref(null);
let meteorCounter = 0;
let animationFrameId = null;

// Create a new meteor with unique properties
const createMeteor = () => {
    // Skip if component is not mounted or meteors are disabled
    if (!meteorContainer.value || !meteorsActive.value || isReducedMotion.value) return;
    
    const containerWidth = meteorContainer.value.offsetWidth;
    const containerHeight = meteorContainer.value.offsetHeight;
    
    // Limit concurrent meteors based on device
    const maxMeteors = isMobile.value ? 1 : 3;
    if (meteors.length >= maxMeteors) return;
    
    // Create meteor with unique properties
    const meteor = {
        id: `meteor-${meteorCounter++}`,
        startX: containerWidth, // Start from right edge
        startY: Math.random() * 30, // Start near top, with some randomness
        length: 30 + Math.random() * 50, // Keep existing length randomness
        opacity: 0.7 + Math.random() * 0.3,
        duration: 800 + Math.random() * 400, // Match 800-1200ms range
        timestamp: Date.now(),
    };
    
    meteors.push(meteor);
    
    // Auto-remove after animation completes
    setTimeout(() => {
        const index = meteors.findIndex(m => m.id === meteor.id);
        if (index !== -1) {
            meteors.splice(index, 1);
        }
    }, meteor.duration + 100); // Add buffer to ensure animation completes
};

// Meteor animation scheduler
const scheduleMeteors = () => {
    if (!meteorContainer.value || !meteorsActive.value) return;
    
    // Determine interval based on device
    const baseInterval = isMobile.value ? 3000 : 1500;
    const interval = baseInterval + Math.random() * 1000;
    
    createMeteor();
    setTimeout(scheduleMeteors, interval);
};

// Start/stop animation system
const startMeteorSystem = () => {
    if (isReducedMotion.value) return;
    meteorsActive.value = true;
    scheduleMeteors();
};

const stopMeteorSystem = () => {
    meteorsActive.value = false;
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
    }
};

// Lifecycle hooks
onMounted(() => {
    handleResize();
    checkPreferences();
    window.addEventListener("resize", handleResize);

    // Pause animations when page is inactive
    document.addEventListener("visibilitychange", () => {
        toggleMeteors(!document.hidden);
    });
    
    // Start meteor system
    startMeteorSystem();
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
    document.removeEventListener("visibilitychange", () => {});
    stopMeteorSystem();
});
</script>

<template>
    <div
        class="flex min-h-32 w-full items-center border-b bg-gradient-to-r from-primary/20 to-bg-content_background/50 backdrop-blur lg:min-h-[160px] border-none bg-cover bg-center overflow-visible"
    >
        <!-- Saturn Decoration with enhanced ring system -->
        <div
            class="absolute right-[7%] md:right-[10%] top-5 md:top-7 lg:top-32"
            aria-hidden="true"
        >
            <div
                class="relative w-10 h-10 overflow-visible transform rounded-full md:w-20 md:h-20 lg:w-32 lg:h-32 opacity-20 bg-secondary rotate-12"
                :class="{ 'animate-none': isReducedMotion }"
            >
                <div
                    class="absolute inset-0 rounded-full opacity-50 bg-gradient-to-tr from-secondary to-primary"
                ></div>
                <div
                    class="absolute inset-[-10px] border-2 border-secondary/30 rounded-full transform -rotate-12"
                ></div>

                <!-- Saturn ring particles -->
                <template v-if="!isReducedMotion">
                    <div
                        v-for="i in 12"
                        :key="`ring-particle-${i}`"
                        class="absolute w-1 h-1 rounded-full bg-primary"
                        :style="{
                            left: `${50 + Math.cos(i * (Math.PI / 6)) * 30}%`,
                            top: `${
                                50 + Math.sin(i * (Math.PI / 6)) * 30 * 0.5
                            }%`,
                            animationDelay: `${i * 0.5}s`,
                            transform: `scale(${0.5 + Math.random() * 0.5})`,
                        }"
                    ></div>
                </template>
            </div>
        </div>

        <!-- Vue-controlled Meteor shower effect -->
        <div
            ref="meteorContainer"
            class="absolute inset-0 overflow-hidden opacity-80 pointer-events-none"
        >
            <div
                v-for="meteor in meteors"
                :key="meteor.id"
                class="absolute meteor-trail"
                :style="{
                    top: `${meteor.startY}%`,
                    left: `${meteor.startX}px`,
                    width: `${meteor.length}px`,
                    height: '2px',
                    transform: 'rotate(45deg)',
                    opacity: meteor.opacity,
                    background: 'linear-gradient(to left, rgba(155, 135, 245, 0.9), transparent)',
                    animation: `meteor-vue ${meteor.duration}ms linear forwards`,
                }"
            ></div>
        </div>

        <!-- Black hole accent (bottom left) -->
        <div
            v-if="!isMobile"
            class="absolute bottom-0 left-0 w-20 h-20 opacity-10 blackhole-effect"
            aria-hidden="true"
        ></div>

        <div class="container py-6 mx-auto max-w-7xl">
            <!-- Stars background (CSS-generated) -->
            <div class="absolute inset-0 overflow-hidden opacity-10">
                <div class="stars-sm"></div>
                <div class="stars-md"></div>
                <div class="stars-lg"></div>
            </div>

            <!-- Product Info - Flex Container 1 -->
            <div class="relative flex items-start gap-2 mx-4">
                <!-- Thumbnail with 3D Effect -->
                <div class="relative perspective-cosmic -top-16 md:-top-28">
                    <img
                        v-if="produk.thumbnail"
                        :src="`/storage/${produk.thumbnail}`"
                        :alt="produk.nama"
                        width="300"
                        height="300"
                        class="z-20 object-cover w-32 shadow-2xl -mb-14 aspect-square rounded-2xl lg:-mb-20 md:w-48 lg:w-60 rotateY-cosmic"
                        loading="lazy"
                    />

                    <!-- Orbiting Planets with enhanced animation -->
                    <div
                        class="absolute w-4 h-4 rounded-full bg-secondary/80 orbit-element-1"
                    ></div>
                    <div
                        class="absolute w-2 h-2 rounded-full bg-primary-hover/80 orbit-element-2"
                    ></div>
                    <div
                        class="absolute w-3 h-3 rounded-full bg-secondary-hover/80 orbit-element-3"
                    ></div>
                </div>

                <!-- Title & Description -->
                <div class="py-4 text-left text-white">
                    <h1 class="text-sm font-bold md:text-3xl cosmic-text-glow">
                        {{ produk.nama }}
                    </h1>
                    <h2
                        v-if="produk.developer"
                        class="mb-4 text-xs md:text-xl text-secondary cosmic-underline"
                    >
                        {{ produk.developer }}
                    </h2>

                    <!-- Product Info - Flex Container 2 (Feature Icons) -->
                    <div
                        class="z-10 hidden gap-6 justify-evenly md:flex md:mt-4"
                    >
                        <!-- Fast Process -->
                        <div
                            class="flex flex-row items-center md:gap-2 text-primary-text"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-secondary/20 cosmic-pulse"
                            >
                                <Rocket class="w-4 h-4 text-primary-text" />
                            </div>
                            <span
                                class="text-[10px] font-medium text-center md:text-sm"
                                >Proses Cepat</span
                            >
                        </div>

                        <!-- 24/7 Support -->
                        <div
                            class="flex flex-row items-center md:gap-2 text-primary-text"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-secondary/20 cosmic-pulse"
                            >
                                <MessageSquare
                                    class="w-4 h-4 text-primary-text"
                                />
                            </div>
                            <span
                                class="text-[10px] font-medium text-center md:text-sm"
                                >Layanan Chat 24/7</span
                            >
                        </div>

                        <!-- Secure Payment -->
                        <div
                            class="flex flex-row items-center md:gap-2 text-primary-text"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-secondary/20 cosmic-pulse"
                            >
                                <Shield class="w-4 h-4 text-primary-text" />
                            </div>
                            <span
                                class="text-[10px] font-medium text-center md:text-sm"
                                >Pembayaran Aman</span
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="z-10 grid grid-cols-3 gap-4 justify-evenly md:hidden">
                <!-- Fast Process -->
                <div
                    class="flex flex-col items-center md:gap-4 md:flex-row text-primary-text"
                >
                    <div
                        class="flex items-center justify-center w-8 h-8 mb-2 rounded-full bg-secondary/20 cosmic-pulse"
                    >
                        <Rocket class="w-4 h-4 text-primary-text" />
                    </div>
                    <span class="text-[10px] font-medium text-center md:text-sm"
                        >Proses Cepat</span
                    >
                </div>

                <!-- 24/7 Support -->
                <div
                    class="flex flex-col items-center md:gap-4 md:flex-row text-primary-text"
                >
                    <div
                        class="flex items-center justify-center w-8 h-8 mb-2 rounded-full bg-secondary/20 cosmic-pulse"
                    >
                        <MessageSquare class="w-4 h-4 text-primary-text" />
                    </div>
                    <span class="text-[10px] font-medium text-center md:text-sm"
                        >Layanan Chat 24/7</span
                    >
                </div>

                <!-- Secure Payment -->
                <div
                    class="flex flex-col items-center md:gap-4 md:flex-row text-primary-text"
                >
                    <div
                        class="flex items-center justify-center w-8 h-8 mb-2 rounded-full bg-secondary/20 cosmic-pulse"
                    >
                        <Shield class="w-4 h-4 text-primary-text" />
                    </div>
                    <span class="text-[10px] font-medium text-center md:text-sm"
                        >Pembayaran Aman</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Cosmic Animation & Effects */
.cosmic-text-glow {
    text-shadow: 0 0 8px rgba(155, 135, 245, 0.5);
}

.cosmic-underline {
    position: relative;
    display: inline-block;
}

.cosmic-underline::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 100%;
    height: 2px;
    background: linear-gradient(
        90deg,
        rgba(51, 195, 240, 0.3),
        rgba(51, 195, 240, 0.8),
        rgba(51, 195, 240, 0.3)
    );
    animation: pulse-animation 3s infinite;
}

.perspective-cosmic {
    perspective: 25em;
}

.rotateY-cosmic {
    position: relative;
    transform: rotateY(20deg) rotateX(-4deg);
    transform-origin: left center;
    transition: transform 0.3s ease;
    will-change: transform;
}

/* Orbiting elements */
.orbit-element-1 {
    top: 10%;
    right: -10px;
    animation: orbit 8s linear infinite;
}

.orbit-element-2 {
    bottom: 30%;
    left: -5px;
    animation: orbit 12s linear infinite reverse;
}

.orbit-element-3 {
    top: 40%;
    right: 10%;
    animation: orbit 15s linear infinite;
}

.cosmic-pulse {
    animation: pulse-subtle 4s ease-in-out infinite;
}

/* Vue-controlled meteor animation */
@keyframes meteor-vue {
    0% {
        transform: translateX(0) translateY(0) rotate(45deg);
        opacity: var(--opacity, 0.8);
    }
    100% {
        transform: translateX(-300%) translateY(300%) rotate(45deg);
        opacity: 0;
    }
}

/* Black hole effect */
.blackhole-effect {
    background: radial-gradient(
        circle at center,
        transparent 0%,
        rgba(0, 0, 0, 0.7) 70%
    );
    border-radius: 50%;
    transform: translateZ(0);
    will-change: transform;
    animation: blackhole-pulse 8s ease-in-out infinite;
}

/* Stars backgrounds */
.stars-sm,
.stars-md,
.stars-lg {
    position: absolute;
    width: 100%;
    height: 100%;
    background-repeat: repeat;
}

.stars-sm {
    background-image: radial-gradient(
        1px 1px at calc(100% * var(--x)) calc(100% * var(--y)),
        white,
        transparent
    );
    background-size: 150px 150px;
    animation: twinkling 4s infinite;
}

.stars-md {
    background-image: radial-gradient(
        1.5px 1.5px at calc(100% * var(--x)) calc(100% * var(--y)),
        white,
        transparent
    );
    background-size: 200px 200px;
    animation: twinkling 6s infinite 1s;
}

.stars-lg {
    background-image: radial-gradient(
        2px 2px at calc(100% * var(--x)) calc(100% * var(--y)),
        white,
        transparent
    );
    background-size: 250px 250px;
    animation: twinkling 8s infinite 2s;
}

/* Animations */
@keyframes orbit {
    from {
        transform: rotate(0deg) translateX(10px) rotate(0deg);
    }
    to {
        transform: rotate(360deg) translateX(10px) rotate(-360deg);
    }
}

@keyframes pulse-animation {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 1;
    }
}

@keyframes pulse-subtle {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.8;
    }
    50% {
        transform: scale(1.05);
        opacity: 1;
    }
}

@keyframes twinkling {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.8;
    }
}

@keyframes blackhole-pulse {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.2;
    }
}

/* Set random star positions using CSS custom properties */
.stars-sm {
    --x: 0.3;
    --y: 0.7;
}

.stars-md {
    --x: 0.7;
    --y: 0.4;
}

.stars-lg {
    --x: 0.5;
    --y: 0.9;
}

/* Responsive rules for animations */
@media (prefers-reduced-motion: reduce) {
    .meteor-trail,
    .orbit-element-1,
    .orbit-element-2,
    .orbit-element-3,
    .cosmic-pulse,
    .blackhole-effect,
    .cosmic-underline::after {
        animation-duration: 10s !important;
        animation-iteration-count: 1 !important;
    }
}
</style>
