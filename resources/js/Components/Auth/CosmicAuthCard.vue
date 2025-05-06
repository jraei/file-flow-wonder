
<script setup>
import { ref } from 'vue';
import CosmicStarfield from '@/Components/Auth/CosmicStarfield.vue';
import CosmicPlanetSystem from '@/Components/Auth/CosmicPlanetSystem.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
});

// For the quantum feedback animation
const isProcessingAuth = ref(false);
const authSuccess = ref(false);
const authFailed = ref(false);

// Expose methods to parent components
defineExpose({
  startProcessing: () => {
    isProcessingAuth.value = true;
  },
  showSuccess: () => {
    authSuccess.value = true;
    setTimeout(() => {
      authSuccess.value = false;
    }, 2000);
  },
  showFailure: () => {
    authFailed.value = true;
    setTimeout(() => {
      authFailed.value = false;
      isProcessingAuth.value = false;
    }, 1500);
  }
});
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center items-center relative px-6 py-10 overflow-hidden bg-content_background">
    <!-- Close button to return home -->
    <Link :href="route('index')" class="absolute top-4 left-4 z-10">
      <div class="p-2 rounded-full bg-primary hover:bg-primary-hover transition-all duration-300 transform hover:scale-105">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>
    </Link>
    
    <!-- Cosmic animations -->
    <CosmicStarfield />
    
    <!-- Planet systems in corners -->
    <div class="absolute top-[5%] right-[10%] opacity-60">
      <CosmicPlanetSystem />
    </div>
    <div class="absolute bottom-[5%] left-[5%] opacity-40">
      <CosmicPlanetSystem />
    </div>
    
    <!-- Auth card container -->
    <div class="w-full max-w-xl z-10 relative">
      <!-- Card header -->
      <div class="text-center mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
          {{ title }}
          <span class="cosmic-text-glow">Cosmic</span>
        </h1>
        <p v-if="subtitle" class="text-gray-300">{{ subtitle }}</p>
      </div>
      
      <!-- Auth card -->
      <div class="cosmic-card-container">
        <div class="cosmic-card-bg"></div>
        <div class="cosmic-card-content p-6 sm:p-8">
          <slot></slot>
        </div>
      </div>
    </div>
    
    <!-- Quantum authentication feedback effects -->
    <div v-if="isProcessingAuth" class="quantum-auth-container">
      <div v-if="authSuccess" class="quantum-success"></div>
      <div v-if="authFailed" class="quantum-failure"></div>
    </div>
  </div>
</template>

<style scoped>
.cosmic-text-glow {
  text-shadow: 0 0 10px rgba(155, 135, 245, 0.8), 0 0 20px rgba(155, 135, 245, 0.4);
}

.cosmic-card-container {
  position: relative;
  border-radius: 0.75rem;
  overflow: hidden;
}

.cosmic-card-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(27, 38, 59, 0.9) 0%, rgba(24, 24, 36, 0.95) 100%);
  border: 1px solid rgba(155, 135, 245, 0.2);
  backdrop-filter: blur(10px);
  box-shadow: 0 0 30px rgba(155, 135, 245, 0.1), inset 0 0 20px rgba(51, 195, 240, 0.05);
  z-index: 0;
}

.cosmic-card-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 50% 0%, rgba(155, 135, 245, 0.15), transparent 70%);
  z-index: -1;
}

.cosmic-card-content {
  position: relative;
  z-index: 1;
}

.quantum-auth-container {
  position: fixed;
  inset: 0;
  z-index: 100;
  pointer-events: none;
}

.quantum-success {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, transparent 30%, rgba(155, 135, 245, 0.3) 70%);
  animation: quantumSuccess 2s forwards;
}

.quantum-failure {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, rgba(24, 24, 36, 0) 0%, rgba(255, 50, 50, 0.2) 100%);
  animation: quantumFailure 1.5s forwards;
}

@keyframes quantumSuccess {
  0% {
    opacity: 0;
    transform: scale(0);
  }
  50% {
    opacity: 1;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(1.5);
  }
}

@keyframes quantumFailure {
  0% {
    opacity: 0;
  }
  20% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

/* Gravitational form field effect will be added by parent components */
:deep(input:focus) ~ .cosmic-gravitational-particles {
  opacity: 1;
  transform: scale(1);
}
</style>
