
<template>
    <div
        class="absolute inset-0 z-20 overflow-hidden pointer-events-none meteor-container"
    >
        <div
            v-for="(meteor, index) in visibleMeteors"
            :key="`meteor-${index}`"
            class="absolute rounded-full bg-primary meteor"
            :style="{
                left: `${meteor.left}px`,
                top: `${meteor.top}px`,
                width: `${meteor.size}px`,
                height: `${meteor.size}px`,
                transform: `rotate(${meteor.rotation}deg)`,
                opacity: meteor.opacity,
                willChange: 'transform, opacity, left, top'
            }"
        >
            <!-- Enhanced meteor trail -->
            <div
                class="absolute top-1/2 left-0 h-[2px] transform -translate-y-1/2"
                :style="{
                    width: `${meteor.trailLength}px`,
                    background: `linear-gradient(to right, ${meteor.trailColor}, transparent)`,
                }"
            ></div>

            <!-- Enhanced fire particles (reduced for performance) -->
            <span
                v-for="particle in meteor.particles.slice(0, isLowPowerDevice ? 2 : 4)"
                :key="`particle-${particle.id}`"
                class="absolute rounded-full animate-ping"
                :style="{
                    height: `${particle.size}px`,
                    width: `${particle.size}px`,
                    left: `${particle.offsetX}px`,
                    top: `${particle.offsetY}px`,
                    backgroundColor: particle.color,
                    animationDuration: `${particle.duration}s`,
                    opacity: particle.opacity,
                }"
            ></span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";

// Device detection
const isMobile = ref(window.innerWidth < 768);
const isLowPowerDevice = computed(() => {
    return navigator.hardwareConcurrency
        ? navigator.hardwareConcurrency < 4
        : isMobile.value;
});

// Meteor count based on device
const meteorCount = computed(() => 
    isMobile.value ? 8 + Math.floor(Math.random() * 4) - 2 : 15 + Math.floor(Math.random() * 10) - 5
);

// Meteor pool - 5 DOM elements reused in cycle
const POOL_SIZE = 5;
const visibleMeteors = ref([]);
const meteorPool = ref([]);

// Animation state
const isTabActive = ref(!document.hidden);
const isPageVisible = ref(true);
let animationId = null;
let lastAnimationTime = 0;

// Generate meteor entry positions - from top-right to bottom-left (160° to 340°)
function generateEntryPosition() {
    // Decide if meteor enters from top or right edge
    const entersFromTop = Math.random() > 0.5;
    
    let left, top;
    
    if (entersFromTop) {
        // Enter from top edge
        left = Math.random() * (window.innerWidth * 0.7) + (window.innerWidth * 0.3); // Right 70% of top edge
        top = -20; // Just above the viewport
    } else {
        // Enter from right edge
        left = window.innerWidth + 20; // Just off the right viewport
        top = Math.random() * (window.innerHeight * 0.7); // Top 70% of right edge
    }
    
    return { left, top };
}

// Generate meteor properties with cached calculations
function generateMeteor() {
    const entryPosition = generateEntryPosition();
    const baseSize = Math.random() * 3 + 2; // 2-5px
    const trailLength = Math.floor(Math.random() * 15 + 75); // 75-90px
    const glowSize = Math.floor(Math.random() * 15 + 10); // 10-25px
    
    // Use primary color
    const glowColor = "rgba(155, 135, 245, 0.8)";
    const trailColor = "rgba(155, 135, 245, 0.8)";
    
    // Create particles (reduced count)
    const particleCount = isLowPowerDevice.value ? 2 : 4;
    const particles = Array(particleCount).fill(0).map((_, i) => ({
        id: i,
        size: Math.random() * 3 + 1.5,
        offsetX: Math.random() * 10 - 20,
        offsetY: Math.random() * 6 - 3,
        color: i % 3 === 0 ? "#FCD34D" : i % 2 === 0 ? "#F97316" : "#EF4444",
        duration: Math.random() * 1.5 + 0.5,
        opacity: Math.random() * 0.7 + 0.3
    }));
    
    // Speed variance (pixels per second)
    const speed = 300 + (Math.random() * 100 - 50);
    
    return {
        ...entryPosition,
        size: baseSize,
        trailLength,
        glow: glowSize,
        glowColor,
        trailColor,
        opacity: Math.random() * 0.2 + 0.8,
        rotation: Math.random() * 20 + 160, // Angle between 160-180 degrees
        particles,
        speed,
        isActive: true,
        progress: 0 // Animation progress (0 to 1)
    };
}

// Initialize the meteor pool
function initMeteorPool() {
    meteorPool.value = Array(meteorCount.value).fill(0).map(() => generateMeteor());
    
    // Start with a few visible meteors (up to POOL_SIZE)
    visibleMeteors.value = meteorPool.value.slice(0, Math.min(POOL_SIZE, meteorPool.value.length));
}

// Handle animation with object pooling
let lastTime = 0;
function animate(timestamp) {
    if (!isTabActive.value || !isPageVisible.value) {
        animationId = requestAnimationFrame(animate);
        return;
    }
    
    // Throttle updates for performance
    const elapsed = timestamp - lastTime;
    if (elapsed < (isLowPowerDevice.value ? 33 : 16)) { // 30fps or 60fps
        animationId = requestAnimationFrame(animate);
        return;
    }
    
    lastTime = timestamp;
    
    // Update meteor positions
    visibleMeteors.value.forEach(meteor => {
        // Calculate new position based on angle and speed
        const radians = (meteor.rotation * Math.PI) / 180;
        const dx = Math.cos(radians) * meteor.speed * elapsed / 1000;
        const dy = Math.sin(radians) * meteor.speed * elapsed / 1000;
        
        meteor.left += dx;
        meteor.top += dy;
        
        meteor.progress += 0.005;
        
        // Check if meteor is off screen
        if (
            meteor.left < -100 || 
            meteor.left > window.innerWidth + 100 ||
            meteor.top > window.innerHeight + 100
        ) {
            // Replace with a new meteor from the pool
            const newMeteorIndex = meteorPool.value.findIndex(m => !visibleMeteors.value.includes(m));
            
            if (newMeteorIndex >= 0) {
                const meteorIndex = visibleMeteors.value.indexOf(meteor);
                const newMeteor = generateMeteor(); // Create a fresh meteor
                meteorPool.value[newMeteorIndex] = newMeteor;
                
                if (meteorIndex >= 0) {
                    visibleMeteors.value[meteorIndex] = newMeteor;
                }
            }
        }
    });
    
    animationId = requestAnimationFrame(animate);
}

// Handle window resize with debounce
let resizeTimeout;
function handleResize() {
    isMobile.value = window.innerWidth < 768;
    
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        // Regenerate meteor pool on significant resize
        initMeteorPool();
    }, 300);
}

// Handle visibility changes
function handleVisibilityChange() {
    isTabActive.value = !document.hidden;
    
    if (isTabActive.value && isPageVisible.value) {
        lastTime = performance.now();
        if (!animationId) {
            animationId = requestAnimationFrame(animate);
        }
    }
}

// Set up intersection observer for performance
function setupVisibilityObserver() {
    const observer = new IntersectionObserver((entries) => {
        const [entry] = entries;
        isPageVisible.value = entry.isIntersecting;
        
        if (isPageVisible.value && isTabActive.value) {
            lastTime = performance.now();
            if (!animationId) {
                animationId = requestAnimationFrame(animate);
            }
        }
    });
    
    const container = document.querySelector('.meteor-container');
    if (container) {
        observer.observe(container);
    }
    
    return observer;
}

let observer;
onMounted(() => {
    initMeteorPool();
    window.addEventListener('resize', handleResize);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Start animation
    lastTime = performance.now();
    animationId = requestAnimationFrame(animate);
    
    // Setup visibility observer
    observer = setupVisibilityObserver();
});

onUnmounted(() => {
    if (animationId) {
        cancelAnimationFrame(animationId);
        animationId = null;
    }
    
    window.removeEventListener('resize', handleResize);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    
    if (observer) {
        observer.disconnect();
    }
});
</script>

<style scoped>
.meteor-container {
    transform: translateZ(0); /* Hardware acceleration */
    mix-blend-mode: plus-lighter; /* Enhanced blending for glow effect */
    z-index: 20;
}

.meteor {
    animation-fill-mode: forwards;
    z-index: 20;
    will-change: transform, opacity, left, top;
}

@media (prefers-reduced-motion: reduce) {
    .meteor {
        animation-duration: 10s !important;
    }

    .meteor:nth-child(even) {
        display: none;
    }
}
</style>
