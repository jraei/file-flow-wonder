
<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const starCount = ref(window.innerWidth < 640 ? 35 : 70);
const canvasRef = ref(null);
let animationFrameId = null;
let isPageActive = true;
let stars = [];
let meteors = [];
let lastMeteorTime = 0;

function initStarfield() {
  const canvas = canvasRef.value;
  if (!canvas) return;
  
  const ctx = canvas.getContext('2d');
  const width = canvas.width;
  const height = canvas.height;
  
  // Create stars
  stars = [];
  for (let i = 0; i < starCount.value; i++) {
    stars.push({
      x: Math.random() * width,
      y: Math.random() * height,
      radius: Math.random() * 1.5 + 0.5,
      opacity: Math.random() * 0.8 + 0.2,
      speed: Math.random() * 0.05 + 0.01
    });
  }
  
  function drawStars() {
    if (!isPageActive) return;
    
    ctx.clearRect(0, 0, width, height);
    
    // Draw stars
    stars.forEach(star => {
      ctx.beginPath();
      ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(255, 255, 255, ${star.opacity})`;
      ctx.fill();
      
      // Move star
      star.y += star.speed;
      
      // Reset if star goes off screen
      if (star.y > height) {
        star.y = 0;
        star.x = Math.random() * width;
      }
    });
    
    // Maybe create a new meteor
    const now = Date.now();
    if (now - lastMeteorTime > (Math.random() * 4000 + 8000)) { // 8-12 seconds
      meteors.push({
        x: Math.random() * width,
        y: 0,
        length: Math.random() * 80 + 20,
        speed: Math.random() * 5 + 3,
        opacity: 1
      });
      lastMeteorTime = now;
    }
    
    // Draw and update meteors
    meteors = meteors.filter(meteor => {
      if (meteor.opacity <= 0) return false;
      
      // Draw meteor
      const gradient = ctx.createLinearGradient(
        meteor.x, meteor.y, 
        meteor.x + meteor.length * 0.7, meteor.y + meteor.length
      );
      gradient.addColorStop(0, `rgba(255, 255, 255, ${meteor.opacity})`);
      gradient.addColorStop(1, `rgba(70, 120, 255, 0)`);
      
      ctx.beginPath();
      ctx.moveTo(meteor.x, meteor.y);
      ctx.lineTo(meteor.x + meteor.length * 0.7, meteor.y + meteor.length);
      ctx.strokeStyle = gradient;
      ctx.lineWidth = 2;
      ctx.stroke();
      
      // Update meteor
      meteor.x += meteor.speed * 0.3;
      meteor.y += meteor.speed;
      meteor.opacity -= 0.02;
      
      return true;
    });
    
    animationFrameId = requestAnimationFrame(drawStars);
  }
  
  drawStars();
}

function resizeCanvas() {
  const canvas = canvasRef.value;
  if (!canvas) return;
  
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  
  // Adjust star count based on viewport
  starCount.value = window.innerWidth < 640 ? 35 : 70;
  
  // Reinitialize after resize
  initStarfield();
}

function handleVisibilityChange() {
  isPageActive = document.visibilityState === 'visible';
  
  // Reset animation if page becomes visible again
  if (isPageActive && canvasRef.value) {
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
    }
    initStarfield();
  }
}

onMounted(() => {
  if (canvasRef.value) {
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    document.addEventListener('visibilitychange', handleVisibilityChange);
  }
});

onUnmounted(() => {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  window.removeEventListener('resize', resizeCanvas);
  document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>

<template>
  <canvas ref="canvasRef" class="starfield-canvas"></canvas>
</template>

<style scoped>
.starfield-canvas {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  pointer-events: none;
}
</style>
