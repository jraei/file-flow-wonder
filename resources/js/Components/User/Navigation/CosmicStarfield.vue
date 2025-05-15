<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const canvasRef = ref(null);
let animationFrameId = null;
let ctx = null;
let stars = [];

const props = defineProps({
    starCount: {
        type: Number,
        default: 50,
    },
    speed: {
        type: Number,
        default: 0.05,
    },
});

function initCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;

    ctx = canvas.getContext("2d");
    resizeCanvas();

    // Create stars
    stars = [];
    for (let i = 0; i < props.starCount; i++) {
        stars.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 1.5 + 0.5,
            opacity: Math.random() * 0.8 + 0.2,
        });
    }

    animate();
}

function resizeCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;

    const parent = canvas.parentElement;
    canvas.width = parent.offsetWidth;
    canvas.height = parent.offsetHeight;
}

function animate() {
    if (!ctx) return;

    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);

    stars.forEach((star) => {
        // Update star position
        star.y += props.speed;

        // If star is out of canvas, reset position
        if (star.y > canvasRef.value.height) {
            star.y = 0;
            star.x = Math.random() * canvasRef.value.width;
        }

        // Draw star
        ctx.fillStyle = `rgba(255, 255, 255, ${star.opacity})`;
        ctx.beginPath();
        ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
        ctx.fill();
    });

    animationFrameId = requestAnimationFrame(animate);
}

onMounted(() => {
    initCanvas();
    window.addEventListener("resize", resizeCanvas);
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    window.removeEventListener("resize", resizeCanvas);
});
</script>

<template>
    <canvas ref="canvasRef" class="absolute inset-0 w-full h-full"></canvas>
</template>
