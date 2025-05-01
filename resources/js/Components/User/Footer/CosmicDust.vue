
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    particleCount: {
        type: Number,
        default: 30
    }
});

const particles = ref([]);
let animationFrame = null;

// Create particles with random positions
const createParticles = () => {
    particles.value = [];
    
    for (let i = 0; i < props.particleCount; i++) {
        particles.value.push({
            x: Math.random() * 100, // percentage of container width
            y: Math.random() * 100, // percentage of container height
            size: 0.5 + Math.random() * 1.5,
            opacity: 0.1 + Math.random() * 0.5,
            velocityX: (Math.random() - 0.5) * 0.05, // slow movement
            velocityY: (Math.random() - 0.5) * 0.05,
            // Randomize animation duration for each particle
            animationDelay: Math.random() * 5
        });
    }
};

// Animation loop
const animate = () => {
    particles.value.forEach(particle => {
        // Brownian motion simulation
        particle.x += particle.velocityX;
        particle.y += particle.velocityY;
        
        // Bounce off boundaries
        if (particle.x <= 0 || particle.x >= 100) {
            particle.velocityX *= -1;
        }
        if (particle.y <= 0 || particle.y >= 100) {
            particle.velocityY *= -1;
        }
        
        // Randomly change direction occasionally
        if (Math.random() < 0.01) {
            particle.velocityX = (Math.random() - 0.5) * 0.05;
            particle.velocityY = (Math.random() - 0.5) * 0.05;
        }
    });
    
    animationFrame = requestAnimationFrame(animate);
};

onMounted(() => {
    createParticles();
    animationFrame = requestAnimationFrame(animate);
});

onUnmounted(() => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
});
</script>

<template>
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div 
            v-for="(particle, index) in particles" 
            :key="`dust-${index}`"
            class="absolute rounded-full bg-white"
            :style="{
                width: `${particle.size}px`,
                height: `${particle.size}px`,
                left: `${particle.x}%`,
                top: `${particle.y}%`,
                opacity: particle.opacity,
                animationDelay: `${particle.animationDelay}s`
            }"
        ></div>
    </div>
</template>
