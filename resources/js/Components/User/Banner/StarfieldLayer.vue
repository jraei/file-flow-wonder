
<template>
    <div class="absolute inset-0 pointer-events-none starfield-container z-5">
        <canvas ref="canvasRef" class="absolute inset-0 w-full h-full"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";

const canvasRef = ref(null);
let animationFrameId = null;
let ctx = null;
let stars = [];
let lastTime = 0;
let frameCount = 0;
const targetFPS = 60;
const throttledFPS = 30;
const frameDuration = 1000 / targetFPS;
const throttledFrameDuration = 1000 / throttledFPS;

// Banner exclusion zone (mask area behind banner image)
const bannerExclusionZone = ref({
    top: 0,
    left: 0,
    width: 0,
    height: 0,
    active: false,
});

const isReducedMotion = computed(() => {
    return (
        window?.matchMedia("(prefers-reduced-motion: reduce)")?.matches || false
    );
});

const isMobile = computed(() => window?.innerWidth < 768);
const isTablet = computed(
    () => window?.innerWidth >= 768 && window?.innerWidth < 1024
);
const isLowPower = computed(() => {
    return navigator.hardwareConcurrency
        ? navigator.hardwareConcurrency < 4
        : isMobile.value;
});

// Determine star count based on device capability with 30% reduction
const getStarCount = computed(() => {
    // 30% fewer stars in visible areas as requested
    const reductionFactor = 0.7;

    if (isLowPower.value || isMobile.value)
        return Math.floor(50 * reductionFactor);
    if (isTablet.value) return Math.floor(30 * reductionFactor);
    return Math.floor(50 * reductionFactor);
});

function initCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;

    ctx = canvas.getContext("2d", { alpha: true });
    resizeCanvas();

    // Create stars with optimized properties
    generateStars();

    // Calculate banner exclusion zone
    calculateBannerExclusionZone();

    // Start animation loop with timing control
    lastTime = performance.now();
    animate();
}

function calculateBannerExclusionZone() {
    // Find banner image
    const bannerImage = document.querySelector(".carousel-container img");
    if (bannerImage) {
        const rect = bannerImage.getBoundingClientRect();
        bannerExclusionZone.value = {
            top: rect.top,
            left: rect.left,
            width: rect.width,
            height: rect.height,
            active: true,
        };
    }
}

function generateStars() {
    stars = [];
    const count = getStarCount.value;

    for (let i = 0; i < count; i++) {
        // Use bias for center density as in original
        const edgeBiasX =
            (Math.random() < 0.5 ? -1 : 1) * Math.pow(Math.random(), 1.5);
        const edgeBiasY =
            (Math.random() < 0.5 ? -1 : 1) * Math.pow(Math.random(), 1.5);

        // Convert to percentage (40% to 60% for more center density)
        const x = 50 + edgeBiasX * 50; // 25% to 75% with center bias
        const y = 50 + edgeBiasY * 50; // 25% to 75% with center bias

        const size = Math.random() * 2 + 1; // 1-3px
        const useSecondary = Math.random() > 0.3;
        const color = useSecondary ? "#33C3F0" : "#ffffff"; // secondary or white

        stars.push({
            x: (x / 100) * (canvasRef.value?.width || 1000),
            y: (y / 100) * (canvasRef.value?.height || 500),
            size,
            color,
            glowSize: size * 2,
            glowColor: useSecondary
                ? "rgba(51, 195, 240, 0.8)"
                : "rgba(255, 255, 255, 0.6)",
            baseOpacity: Math.random() * 0.4 + 0.6, // 0.6-1.0
            currentOpacity: 0,
            pulseDirection: 1,
            pulseSpeed: Math.random() * 0.01 + 0.005,
        });
    }
}

function resizeCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;

    // Use devicePixelRatio for crisp rendering
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;

    // Scale context for high DPI displays
    ctx.scale(dpr, dpr);

    // Regenerate stars for new canvas size
    if (stars.length > 0) {
        generateStars();
    }

    // Recalculate banner exclusion zone
    calculateBannerExclusionZone();
}

function isInExclusionZone(x, y) {
    if (!bannerExclusionZone.value.active) return false;

    const zone = bannerExclusionZone.value;
    return (
        x >= zone.left &&
        x <= zone.left + zone.width &&
        y >= zone.top &&
        y <= zone.top + zone.height
    );
}

function drawStar(star) {
    // Skip if off-screen
    if (
        star.x < 0 ||
        star.x > canvasRef.value.width ||
        star.y < 0 ||
        star.y > canvasRef.value.height
    ) {
        return;
    }

    // Skip if in exclusion zone
    if (isInExclusionZone(star.x, star.y)) {
        return;
    }

    // Update opacity for pulsing effect
    if (star.pulseDirection > 0) {
        star.currentOpacity += star.pulseSpeed;
        if (star.currentOpacity >= 1) {
            star.pulseDirection = -1;
        }
    } else {
        star.currentOpacity -= star.pulseSpeed;
        if (star.currentOpacity <= 0.6) {
            star.pulseDirection = 1;
        }
    }

    // Clamp opacity
    star.currentOpacity = Math.max(0.6, Math.min(1, star.currentOpacity));

    // Draw glow effect first (underneath)
    ctx.globalAlpha = star.currentOpacity * 0.7;
    ctx.fillStyle = star.glowColor;
    ctx.beginPath();
    ctx.arc(star.x, star.y, star.glowSize, 0, Math.PI * 2);
    ctx.fill();

    // Draw star
    ctx.globalAlpha = star.currentOpacity;
    ctx.fillStyle = star.color;
    ctx.beginPath();
    ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
    ctx.fill();

    // Reset global alpha
    ctx.globalAlpha = 1;
}

function animate(timestamp = 0) {
    // Determine correct frame duration based on tab visibility
    const currentFrameDuration = document.hidden ? throttledFrameDuration : frameDuration;
    
    // Throttle frame rate for performance
    const elapsed = timestamp - lastTime;

    if (elapsed > currentFrameDuration) {
        lastTime = timestamp - (elapsed % currentFrameDuration);

        // Clear with proper alpha handling
        if (!canvasRef.value || !ctx) return;
        
        ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);

        // Draw all stars
        stars.forEach(drawStar);

        // Performance monitoring
        if (process.env.NODE_ENV !== 'production') {
            frameCount++;
            const drawTime = performance.now() - timestamp;
            if (drawTime > 5 && frameCount % 60 === 0) {
                console.warn(
                    `StarfieldLayer paint time: ${drawTime.toFixed(
                        2
                    )}ms (limit: 2ms)`
                );
            }
        }
    }

    animationFrameId = requestAnimationFrame(animate);
}

// Use intersection observer for performance
let observer = null;

function setupVisibilityObserver() {
    observer = new IntersectionObserver((entries) => {
        const [entry] = entries;

        if (entry.isIntersecting) {
            // Restart animation when visible
            if (!animationFrameId && canvasRef.value) {
                lastTime = performance.now();
                animate();
            }
        } else {
            // Pause animation when not visible
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        }
    });

    if (canvasRef.value) {
        observer.observe(canvasRef.value);
    }
}

// Lifecycle hooks
onMounted(() => {
    initCanvas();
    window.addEventListener("resize", resizeCanvas);

    // Setup visibility observer for performance
    setupVisibilityObserver();

    // Reduce animation load when tab is not visible
    document.addEventListener("visibilitychange", () => {
        if (document.hidden) {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        } else if (!animationFrameId && canvasRef.value) {
            lastTime = performance.now();
            animate();
        }
    });
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    window.removeEventListener("resize", resizeCanvas);

    if (observer) {
        observer.disconnect();
    }
});
</script>

<style scoped>
.starfield-container {
    z-index: 5;
    transform: translateZ(0); /* Hardware acceleration */
}
</style>
