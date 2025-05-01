<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// Rocket state
const isVisible = ref(true);
const rocketPositionX = ref(0);
const rocketPositionY = ref(0);
const rocketRotation = ref(0);
const animationFrame = ref(null);

// Trail and particle system
const particles = ref([]);
const maxParticles = 40;

// Parameters for elliptical path
const centerX = ref(0);
const centerY = ref(0);
const radiusX = ref(300);
const radiusY = ref(150);
let angle = 0;
let speed = 0.0005; // Base speed

// Functions to handle browser tab visibility changes
const handleVisibilityChange = () => {
    if (document.hidden) {
        pauseAnimation();
    } else {
        resumeAnimation();
    }
};

// Create a particle
const createParticle = () => {
    const size = Math.random() * 3 + 1;
    particles.value.push({
        x: rocketPositionX.value,
        y: rocketPositionY.value,
        size,
        opacity: 0.8,
        life: 1.5, // Lifespan in seconds
        speed: {
            x: (Math.random() - 0.5) * 0.5,
            y: (Math.random() - 0.5) * 0.5
        }
    });
    
    // Keep particle count under control
    if (particles.value.length > maxParticles) {
        particles.value.shift();
    }
};

// Update particle positions and properties
const updateParticles = (deltaTime) => {
    particles.value.forEach((particle, index) => {
        // Update life
        particle.life -= deltaTime;
        
        // Update opacity based on life
        particle.opacity = particle.life / 1.5;
        
        // Move particles
        particle.x += particle.speed.x;
        particle.y += particle.speed.y;
        
        // Remove dead particles
        if (particle.life <= 0) {
            particles.value.splice(index, 1);
        }
    });
};

// Animation loop
let lastTime = 0;
const animate = (time) => {
    // Calculate delta time for smoother animations
    const deltaTime = (time - lastTime) / 1000;
    lastTime = time;
    
    // Update rocket position in elliptical orbit
    angle += speed * deltaTime * 60; // Normalize by framerate 
    
    // Calculate position on the ellipse
    rocketPositionX.value = centerX.value + radiusX.value * Math.cos(angle);
    rocketPositionY.value = centerY.value + radiusY.value * Math.sin(angle);
    
    // Calculate rotation to follow the path
    rocketRotation.value = Math.atan2(
        radiusY.value * Math.cos(angle),
        -radiusX.value * Math.sin(angle)
    ) * (180 / Math.PI);
    
    // Create new particles at intervals
    if (Math.random() > 0.7) {
        createParticle();
    }
    
    // Update existing particles
    updateParticles(deltaTime);
    
    // Continue animation loop
    animationFrame.value = requestAnimationFrame(animate);
};

const pauseAnimation = () => {
    if (animationFrame.value !== null) {
        cancelAnimationFrame(animationFrame.value);
        animationFrame.value = null;
    }
};

const resumeAnimation = () => {
    if (animationFrame.value === null) {
        lastTime = performance.now();
        animationFrame.value = requestAnimationFrame(animate);
    }
};

// Adjust path parameters based on screen size
const updatePathParameters = () => {
    const width = window.innerWidth;
    centerX.value = width / 2;
    centerY.value = 100; // Fixed distance from top of container
    
    // Adjust ellipse size based on screen width
    if (width < 640) {
        radiusX.value = width * 0.4;
        radiusY.value = 70;
        speed = 0.0005 * 0.5; // 50% slower on mobile
    } else if (width < 1024) {
        radiusX.value = width * 0.35;
        radiusY.value = 100;
        speed = 0.0005 * 0.75; // 75% speed on tablets
    } else {
        radiusX.value = width * 0.3;
        radiusY.value = 150;
        speed = 0.0005; // Full speed on desktop
    }
};

// Lifecycle hooks
onMounted(() => {
    // Set initial center position and update path
    updatePathParameters();
    
    // Set initial rocket position
    rocketPositionX.value = centerX.value + radiusX.value;
    rocketPositionY.value = centerY.value;
    
    // Start animation
    animationFrame.value = requestAnimationFrame(animate);
    
    // Add event listeners
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('resize', updatePathParameters);
});

onUnmounted(() => {
    // Clean up
    if (animationFrame.value !== null) {
        cancelAnimationFrame(animationFrame.value);
    }
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('resize', updatePathParameters);
});
</script>

<template>
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Rocket with engine trail -->
        <div 
            v-if="isVisible" 
            class="absolute transition-transform duration-100"
            :style="{
                transform: `translate(${rocketPositionX}px, ${rocketPositionY}px) rotate(${rocketRotation}deg)`,
                zIndex: 20
            }"
        >
            <!-- Rocket SVG -->
            <svg 
                width="32" 
                height="32" 
                viewBox="0 0 24 24" 
                fill="none" 
                xmlns="http://www.w3.org/2000/svg"
                class="origin-center"
            >
                <path d="M4.5 18.5L2 22L12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.8214 2.48697 15.5291 3.33782 17L4.5 18.5Z" 
                      fill="#9b87f5" 
                      stroke="white" 
                      stroke-width="1.5" 
                      stroke-linecap="round" 
                      stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="4" fill="#33C3F0" stroke="white" stroke-width="1.5"/>
                <path d="M12 12L16.5 7.5" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            
            <!-- Engine glow effect -->
            <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-primary rounded-full filter blur-sm animate-pulse"></div>
        </div>
        
        <!-- Particle trail system -->
        <div 
            v-for="(particle, index) in particles" 
            :key="`particle-${index}`"
            class="absolute rounded-full bg-primary"
            :style="{
                width: `${particle.size}px`,
                height: `${particle.size}px`,
                transform: `translate(${particle.x}px, ${particle.y}px)`,
                opacity: particle.opacity,
                boxShadow: '0 0 6px rgba(155, 135, 245, 0.8)'
            }"
        ></div>
    </div>
</template>
