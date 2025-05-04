
<template>
  <div>
    <h2 class="text-lg font-semibold text-white mb-2">Progress Transaksi</h2>
    
    <!-- Desktop Timeline (horizontal) -->
    <div class="hidden md:flex items-center justify-between relative mb-8">
      <!-- Line connecting stages -->
      <div class="absolute h-1 bg-gray-700 top-1/2 left-0 right-0 transform -translate-y-1/2 z-0"></div>
      
      <!-- Timeline stages -->
      <div 
        v-for="stage in 4" 
        :key="stage"
        class="z-10 flex flex-col items-center relative"
      >
        <div 
          class="w-12 h-12 rounded-full flex items-center justify-center mb-2 relative z-10"
          :class="[
            stage < currentStage ? 'bg-green-500' : 
            stage === currentStage ? 'bg-primary animate-pulse' : 
            'bg-gray-700'
          ]"
        >
          <!-- Stage 1: Created -->
          <template v-if="stage === 1">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="16"></line>
              <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
          </template>
          
          <!-- Stage 2: Payment -->
          <template v-else-if="stage === 2">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <circle cx="12" cy="12" r="10"></circle>
              <circle cx="12" cy="12" r="2"></circle>
            </svg>
          </template>
          
          <!-- Stage 3: Processing -->
          <template v-else-if="stage === 3">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
            </svg>
          </template>
          
          <!-- Stage 4: Completed -->
          <template v-else-if="stage === 4">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </template>
        </div>
        
        <div class="text-center">
          <div class="text-sm font-medium" :class="stage <= currentStage ? 'text-primary' : 'text-gray-400'">
            {{ stageLabels[stage] }}
          </div>
          <div class="text-xs mt-1 max-w-[120px]" :class="stage <= currentStage ? 'text-gray-200' : 'text-gray-500'">
            {{ stageDescriptions[stage] }}
          </div>
        </div>
      </div>
    </div>
    
    <!-- Mobile Timeline (vertical) -->
    <div class="md:hidden flex flex-col mb-8 relative">
      <div 
        v-for="stage in 4" 
        :key="stage"
        class="flex items-start mb-6 relative"
      >
        <!-- Vertical line connecting stages -->
        <div
          v-if="stage < 4"
          class="absolute bg-gray-700 w-0.5 top-12 bottom-0 left-6 transform -translate-x-1/2 z-0"
        ></div>
        
        <div 
          class="w-12 h-12 rounded-full flex items-center justify-center mr-4 relative z-10"
          :class="[
            stage < currentStage ? 'bg-green-500' : 
            stage === currentStage ? 'bg-primary animate-pulse' : 
            'bg-gray-700'
          ]"
        >
          <!-- Icons same as desktop -->
          <template v-if="stage === 1">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="16"></line>
              <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
          </template>
          
          <template v-else-if="stage === 2">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <circle cx="12" cy="12" r="10"></circle>
              <circle cx="12" cy="12" r="2"></circle>
            </svg>
          </template>
          
          <template v-else-if="stage === 3">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white animate-spin" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
            </svg>
          </template>
          
          <template v-else-if="stage === 4">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-6 w-6 text-white" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </template>
        </div>
        
        <div>
          <div class="text-sm font-medium" :class="stage <= currentStage ? 'text-primary' : 'text-gray-400'">
            {{ stageLabels[stage] }}
          </div>
          <div class="text-xs mt-1" :class="stage <= currentStage ? 'text-gray-200' : 'text-gray-500'">
            {{ stageDescriptions[stage] }}
          </div>
        </div>
      </div>
    </div>
    
    <!-- Countdown timer (only shown when in payment stage) -->
    <div v-if="currentStage === 2 && pembayaran && pembayaran.expired_time" class="bg-dark-card border border-secondary/20 rounded-md px-4 py-2 mb-6">
      <div class="text-white text-center font-medium">
        <span class="countdown-timer bg-secondary/10 px-3 py-1 rounded-md">
          {{ formatExpiration(pembayaran.expired_time) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  currentStage: {
    type: Number,
    default: 1
  },
  stageLabels: {
    type: Object,
    required: true
  },
  stageDescriptions: {
    type: Object,
    required: true
  },
  pembayaran: {
    type: Object,
    default: null
  }
});

// Timer for payment expiration
let timerInterval = null;
const expiryTime = ref(null);

onMounted(() => {
  if (props.pembayaran && props.pembayaran.expired_time && props.currentStage === 2) {
    startCountdown();
  }
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

const startCountdown = () => {
  const targetTime = new Date(props.pembayaran.expired_time).getTime();
  
  timerInterval = setInterval(() => {
    const now = new Date().getTime();
    const distance = targetTime - now;
    
    if (distance <= 0) {
      clearInterval(timerInterval);
      expiryTime.value = "Expired";
      return;
    }
    
    const hours = Math.floor(distance / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    expiryTime.value = `${hours}h ${minutes}m ${seconds}s`;
  }, 1000);
};

// Format the expiration time
const formatExpiration = (expiryDate) => {
  if (!expiryDate) return "";
  
  const targetDate = new Date(expiryDate);
  const now = new Date();
  
  // If already expired
  if (targetDate <= now) return "Expired";
  
  const diffMs = targetDate - now;
  const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
  const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
  const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);
  
  return `${diffHrs} Jam ${diffMins} Menit ${diffSecs} Detik`;
};
</script>
