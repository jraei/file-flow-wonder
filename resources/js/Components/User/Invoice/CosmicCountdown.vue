
<template>
  <div class="flex flex-col items-center">
    <div class="text-xs text-gray-400 mb-1">Waktu pembayaran tersisa:</div>
    <div
      :class="[
        'text-lg font-bold',
        timeRemaining.total <= 3600000 ? 'text-red-500' : 'text-primary',
      ]"
    >
      <span v-if="timeRemaining.days > 0">{{ timeRemaining.days }}d </span>
      {{ timeRemaining.hours }}:{{ timeRemaining.minutes }}:{{ timeRemaining.seconds }}
    </div>
    <div
      v-if="timeRemaining.total <= 3600000"
      class="text-xs text-red-400 mt-1 animate-pulse"
    >
      Segera selesaikan pembayaran!
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
  expiryTime: {
    type: String,
    required: true,
  },
});

const now = ref(new Date().getTime());
let interval = null;

onMounted(() => {
  interval = setInterval(() => {
    now.value = new Date().getTime();
  }, 1000);
});

onBeforeUnmount(() => {
  if (interval) {
    clearInterval(interval);
  }
});

const timeRemaining = computed(() => {
  const expiry = new Date(props.expiryTime).getTime();
  const total = Math.max(0, expiry - now.value);

  const days = Math.floor(total / (1000 * 60 * 60 * 24));
  const hours = String(Math.floor((total % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
  const minutes = String(Math.floor((total % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
  const seconds = String(Math.floor((total % (1000 * 60)) / 1000)).padStart(2, '0');

  return {
    total,
    days,
    hours,
    minutes,
    seconds,
  };
});
</script>
