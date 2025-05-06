
<script setup>
import { ref, onMounted } from 'vue';

const isMobile = ref(window.innerWidth < 640);

onMounted(() => {
  const handleResize = () => {
    isMobile.value = window.innerWidth < 640;
  };
  
  window.addEventListener('resize', handleResize);
  
  return () => {
    window.removeEventListener('resize', handleResize);
  };
});
</script>

<template>
  <div class="cosmic-planet-system" :class="{ 'cosmic-planet-system--mobile': isMobile }">
    <div class="planet">
      <div class="planet-glow"></div>
    </div>
    <div class="orbit">
      <div class="moon"></div>
    </div>
  </div>
</template>

<style scoped>
.cosmic-planet-system {
  position: absolute;
  z-index: 0;
  width: 250px;
  height: 250px;
  pointer-events: none;
}

.cosmic-planet-system--mobile {
  width: 150px;
  height: 150px;
  opacity: 0.6;
}

.planet {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #9b87f5, #33C3F0);
  transform: translate(-50%, -50%) rotate(0deg);
  animation: rotate 120s linear infinite;
  box-shadow: 0 0 20px rgba(155, 135, 245, 0.5), inset 0 0 30px rgba(51, 195, 240, 0.5);
}

.cosmic-planet-system--mobile .planet {
  width: 60px;
  height: 60px;
}

.planet-glow {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: transparent;
  box-shadow: 0 0 60px rgba(155, 135, 245, 0.3);
  animation: pulse 8s ease-in-out infinite alternate;
}

.orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 200px;
  height: 200px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transform: translate(-50%, -50%);
}

.cosmic-planet-system--mobile .orbit {
  width: 120px;
  height: 120px;
}

.moon {
  position: absolute;
  top: -10px;
  left: 50%;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #ffffff, #a897f6);
  transform: translateX(-50%);
  animation: orbit 300s linear infinite;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.6);
}

.cosmic-planet-system--mobile .moon {
  width: 12px;
  height: 12px;
}

@keyframes rotate {
  from { transform: translate(-50%, -50%) rotate(0deg); }
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

@keyframes orbit {
  from { transform: translateX(-50%) rotate(0deg) translateX(100px) rotate(0deg); }
  to { transform: translateX(-50%) rotate(360deg) translateX(100px) rotate(-360deg); }
}

@keyframes pulse {
  0% { opacity: 0.3; }
  50% { opacity: 0.7; }
  100% { opacity: 0.3; }
}

.cosmic-planet-system--mobile .moon {
  animation: orbit-mobile 300s linear infinite;
}

@keyframes orbit-mobile {
  from { transform: translateX(-50%) rotate(0deg) translateX(60px) rotate(0deg); }
  to { transform: translateX(-50%) rotate(360deg) translateX(60px) rotate(-360deg); }
}

@media (prefers-reduced-motion: reduce) {
  .planet, .moon, .planet-glow {
    animation: none;
  }
}
</style>
