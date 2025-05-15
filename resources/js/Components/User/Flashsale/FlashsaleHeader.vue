<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
    eventName: {
        type: String,
        required: true,
    },
    endTime: {
        type: Number,
        required: true,
    },
    serverTime: {
        type: String,
        required: true,
    },
});

const remainingTime = ref({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
});

let countdownInterval = null;
const serverClientDiff = ref(0);

// Sync client time with server time
onMounted(() => {
    const serverDate = new Date(props.serverTime).getTime();
    const clientDate = Date.now();
    serverClientDiff.value = serverDate - clientDate;

    startCountdown();
});

onUnmounted(() => {
    clearInterval(countdownInterval);
});

const startCountdown = () => {
    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 1000);
};

const updateCountdown = () => {
    const now = Date.now() + serverClientDiff.value;
    const timeLeft = props.endTime - now;

    if (timeLeft <= 0) {
        remainingTime.value = {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0,
        };
        clearInterval(countdownInterval);
        return;
    }

    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
    const hours = Math.floor(
        (timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
    );
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

    remainingTime.value = { days, hours, minutes, seconds };
};

// Check if less than 1 hour remains (for alert state)
const isAlmostEnding = computed(() => {
    return remainingTime.value.days === 0 && remainingTime.value.hours === 0;
});

// Format numbers with leading zeros
const formatNumber = (num) => {
    return num.toString().padStart(2, "0");
};
</script>

<template>
    <div class="mb-6">
        <!-- Event Title with Lightning Bolt Icon -->
        <div class="flex items-center mb-4 space-x-2">
            <div class="lightning-icon-container">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="lightning-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold md:text-3xl text-primary glow-text">
                {{ eventName }}
            </h2>
        </div>

        <!-- Countdown timer -->
        <div class="flex items-center justify-start mb-2 space-x-3">
            <div class="timer-container flex space-x-1.5">
                <!-- Days -->
                <div class="flex flex-col items-center">
                    <div
                        class="timer-card"
                        :class="{ 'text-red-500': isAlmostEnding }"
                    >
                        {{ formatNumber(remainingTime.days) }}
                    </div>
                    <span class="text-xs text-gray-300">Hari</span>
                </div>

                <div class="self-start mt-1 text-lg font-bold text-white">
                    :
                </div>

                <!-- Hours -->
                <div class="flex flex-col items-center">
                    <div
                        class="timer-card"
                        :class="{ 'text-red-500': isAlmostEnding }"
                    >
                        {{ formatNumber(remainingTime.hours) }}
                    </div>
                    <span class="text-xs text-gray-300">Jam</span>
                </div>

                <div class="self-start mt-1 text-lg font-bold text-white">
                    :
                </div>

                <!-- Minutes -->
                <div class="flex flex-col items-center">
                    <div
                        class="timer-card"
                        :class="{ 'text-red-500': isAlmostEnding }"
                    >
                        {{ formatNumber(remainingTime.minutes) }}
                    </div>
                    <span class="text-xs text-gray-300">Menit</span>
                </div>

                <div class="self-start mt-1 text-lg font-bold text-white">
                    :
                </div>

                <!-- Seconds -->
                <div class="flex flex-col items-center">
                    <div
                        class="timer-card"
                        :class="{ 'text-red-500': isAlmostEnding }"
                    >
                        {{ formatNumber(remainingTime.seconds) }}
                    </div>
                    <span class="text-xs text-gray-300">Detik</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.glow-text {
    text-shadow: 0 0 10px rgba(155, 135, 245, 0.7);
}

.lightning-icon-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(155, 135, 245, 0.15);
    border-radius: 50%;
    border: 1px solid rgba(155, 135, 245, 0.3);
}

.lightning-icon {
    width: 24px;
    height: 24px;
    color: #33c3f0; /* secondary color */
    filter: drop-shadow(0 0 5px rgba(51, 195, 240, 0.7));
}

.timer-card {
    background-color: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(155, 135, 245, 0.3);
    border-radius: 4px;
    padding: 4px 8px;
    min-width: 40px;
    text-align: center;
    color: white;
    font-weight: bold;
    font-size: 1.25rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(155, 135, 245, 0.3);
}

.timer-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(155, 135, 245, 0.2),
        transparent
    );
    z-index: 1;
}
</style>
