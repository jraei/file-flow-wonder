
<template>
  <div 
    class="relative overflow-hidden rounded-xl transition-all duration-500 group"
    :class="[
      isTopThree 
        ? 'border border-primary/30 bg-gradient-to-b from-primary/20 to-dark-DEFAULT shadow-glow-primary'
        : 'border border-white/10 bg-dark-card hover:border-white/20'
    ]"
  >
    <!-- Cosmic background decorations -->
    <div class="absolute inset-0 -z-10 overflow-hidden">
      <!-- Star field for all cards -->
      <div v-for="i in 20" :key="`star-${i}`" 
        class="absolute w-0.5 h-0.5 bg-white rounded-full opacity-30"
        :style="{
          top: `${Math.random() * 100}%`, 
          left: `${Math.random() * 100}%`,
          animationDelay: `${Math.random() * 5}s`
        }"
        :class="{'animate-pulse-slow': true}"
      ></div>
      
      <!-- Special effects for top 3 -->
      <template v-if="isTopThree">
        <!-- Black hole animation for rank 1 -->
        <div v-if="rank === 1" class="absolute -right-20 -bottom-20 w-40 h-40 rounded-full bg-black opacity-50 animate-pulse-slow"></div>
        
        <!-- Neutron star for rank 2 -->
        <div v-if="rank === 2" class="absolute right-2 top-2 w-6 h-6 rounded-full bg-secondary opacity-20 animate-ping-small"></div>
        
        <!-- Galaxy spiral for rank 3 -->
        <div v-if="rank === 3" class="absolute -left-10 -bottom-10 w-20 h-20 rounded-full border border-secondary/20 opacity-30 animate-spin-slow"></div>
      </template>
    </div>

    <!-- Card content -->
    <div class="p-4 relative z-10">
      <!-- Rank badge -->
      <div 
        class="absolute top-3 left-3 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full text-lg font-bold"
        :class="getRankBadgeClass"
      >
        {{ rank }}
      </div>
      
      <!-- User info -->
      <div class="ml-10 md:ml-12 flex items-center">
        <!-- Avatar placeholder -->
        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-primary/30 border border-white/20 flex items-center justify-center text-white">
          {{ getUserInitials }}
        </div>
        
        <div class="ml-3 flex-1">
          <p class="text-white font-medium truncate" :class="{'text-lg': isTopThree}">{{ displayUsername }}</p>
          <p class="text-secondary text-sm md:text-base font-mono" 
            ref="amountRef" 
            :data-value="formattedTotal">
            {{ formattedTotal }}
          </p>
        </div>
      </div>
    </div>

    <!-- Particle burst effect on hover -->
    <div class="absolute inset-0 pointer-events-none">
      <div v-for="i in (isTopThree ? 12 : 6)" :key="`particle-${i}`"
        class="absolute w-1 h-1 rounded-full bg-primary opacity-0 group-hover:opacity-70"
        :style="{
          top: '50%',
          left: '50%',
          transform: 'scale(0)',
          transition: 'all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1)',
        }"
        :class="{'group-hover:animate-float': true}"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  rank: {
    type: Number,
    required: true
  },
  username: {
    type: String,
    required: true
  },
  total: {
    type: [Number, String],
    required: true
  },
  formattedTotal: {
    type: String,
    required: true
  },
  isTopThree: {
    type: Boolean,
    default: false
  }
});

const amountRef = ref(null);

// Get user initials for avatar
const getUserInitials = computed(() => {
  return props.username.slice(0, 2).toUpperCase();
});

// Display username with special style for top ranks
const displayUsername = computed(() => {
  return props.username;
});

// Dynamic rank badge styling
const getRankBadgeClass = computed(() => {
  if (props.rank === 1) {
    return 'bg-yellow-500 text-black shadow-lg';
  } else if (props.rank === 2) {
    return 'bg-gray-300 text-black';
  } else if (props.rank === 3) {
    return 'bg-amber-700 text-white';
  } else {
    return 'bg-gray-700 text-gray-300';
  }
});

// Animate the counter from 0 to final value
const animateValue = (start, end, duration) => {
  if (!amountRef.value) return;
  
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    const currentValue = Math.floor(progress * (end - start) + start);
    
    // Format as Rupiah
    amountRef.value.textContent = 'Rp ' + currentValue.toLocaleString('id-ID');
    
    if (progress < 1) {
      window.requestAnimationFrame(step);
    } else {
      amountRef.value.textContent = props.formattedTotal;
    }
  };
  
  window.requestAnimationFrame(step);
};

onMounted(() => {
  // Animate value from 0 to actual total
  setTimeout(() => {
    animateValue(0, parseInt(props.total), 1500);
  }, props.rank * 100); // Staggered animation
});

watch(() => props.total, (newVal) => {
  animateValue(0, parseInt(newVal), 1500);
});
</script>
