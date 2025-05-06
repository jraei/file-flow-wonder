<template>
    <div class="cosmic-stars">
        <!-- Star field backdrop -->
        <div
            v-for="i in 20"
            :key="`star-${i}`"
            class="star"
            :style="{
                top: `${Math.random() * 100}%`,
                left: `${Math.random() * 100}%`,
                width: `${Math.random() * 2 + 1}px`,
                height: `${Math.random() * 2 + 1}px`,
                animationDelay: `${Math.random() * 10}s`,
                animationDuration: `${Math.random() * 10 + 10}s`,
            }"
        ></div>

        <!-- Shooting stars -->
        <div
            v-for="i in 5"
            :key="`shooting-${i}`"
            class="shooting-star"
            :style="{
                top: `${Math.random() * 50}%`,
                left: `${Math.random() * 100}%`,
                width: `${Math.random() * 100 + 50}px`,
                height: '1px',
                animationDelay: `${Math.random() * 20}s`,
                animationDuration: `${Math.random() * 10 + 5}s`,
                transform: `rotate(${Math.random() * 45}deg)`,
            }"
        ></div>

        <!-- Nebula clouds -->
        <div
            v-for="i in 3"
            :key="`nebula-${i}`"
            class="nebula"
            :style="{
                top: `${Math.random() * 100}%`,
                left: `${Math.random() * 100}%`,
                width: `${Math.random() * 300 + 100}px`,
                height: `${Math.random() * 200 + 50}px`,
                background:
                    i % 2 === 0
                        ? 'radial-gradient(ellipse at center, rgba(155,135,245,0.1) 0%, rgba(155,135,245,0) 70%)'
                        : 'radial-gradient(ellipse at center, rgba(51,195,240,0.05) 0%, rgba(51,195,240,0) 70%)',
                animationDelay: `${Math.random() * 5}s`,
                transform: `rotate(${Math.random() * 360}deg) scale(${
                    Math.random() * 0.5 + 0.5
                })`,
            }"
        ></div>
    </div>
</template>

<style scoped>
.cosmic-stars {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
    pointer-events: none;
}

.star {
    position: absolute;
    background-color: white;
    border-radius: 50%;
    opacity: 0.4;
    animation: twinkle 10s infinite ease-in-out;
}

.shooting-star {
    position: absolute;
    background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.8) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    opacity: 0;
    animation: shoot 10s infinite ease-out;
}

.nebula {
    position: absolute;
    border-radius: 50%;
    opacity: 0.5;
    animation: float 20s infinite ease-in-out;
    filter: blur(20px);
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.2;
        transform: scale(0.8);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.1);
    }
}

@keyframes shoot {
    0% {
        opacity: 0;
        transform: translateX(-100px);
    }
    5% {
        opacity: 1;
    }
    10% {
        opacity: 0;
        transform: translateX(300px);
    }
    100% {
        opacity: 0;
        transform: translateX(300px);
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(-10px) scale(1);
        opacity: 0.5;
    }
    50% {
        transform: translateY(10px) scale(1.05);
        opacity: 0.7;
    }
}
</style>
